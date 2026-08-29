# BUGFIXES — разбор ошибок и уроки

Живой документ: сюда попадают баги с неочевидной причиной, чтобы не наступать повторно.

---

## 1. `serializable_classes => false` ломает unserialize Eloquent-моделей из кеша

**Дата:** 28.08.2026 · **Коммит:** `cdbba16` · **Среда:** Docker-деплой (Redis-кеш)

### Симптом
На развёрнутом сайте падает всё, что читает объекты из кеша: `__PHP_Incomplete_Class` вместо моделей, ошибки «trying to access a property of an incomplete object».

### Причина
В `config/cache.php` стояло `'serializable_classes' => false`. В Laravel этот параметр пробрасывается в `allowed_classes` нативного `unserialize()`:

```php
// vendor/laravel/framework/src/Illuminate/Cache/RedisStore.php
if ($this->serializableClasses !== null) {
    return unserialize($value, ['allowed_classes' => $this->serializableClasses]);
}
```

`allowed_classes => false` — это НЕ «запретить опасные классы», это «запретить ВСЕ классы»: PHP возвращает любой объект как `__PHP_Incomplete_Class`. У нас `rememberForever('nav_categories', ...)` кеширует Eloquent-коллекцию моделей `Category` — после первого попадания в кеш меню категорий умирало.

Три значения параметра:
- `null` (дефолт) — обычный `unserialize()`, всё работает
- `['App\Models\...']` — allowlist: перечисленным классам разрешено, остальные → incomplete
- `false` — запрет всего: ни один объект не десериализуется

### Фикс
`'serializable_classes' => null`. Если когда-нибудь захочется allowlist — в него придётся внести ВСЕ классы, попадающие в кеш, включая Eloquent-модели и их внутренние зависимости (та ещё охота).

### Урок
`allowed_classes: false` в unserialize — почти никогда не то, что хочется. Дефолтный конфиг Laravel не трогать без понимания, куда значение пробрасывается.

---

## 2. Intervention Image v4: `read()` и `toJpeg()` не существуют

**Дата:** 29.08.2026 · **Найдено:** PHPStan (level 5) · **Файл:** `app/Http/Controllers/AdImageController.php`

### Симптом (скрытый)
Код загрузки картинок к товарам написан под Intervention v3. Во время миграции на v4 API поменялся, но вызовы остались — при первой же загрузке фото товаром вылетел бы `Error: Call to undefined method`.

### Изменения API v3 → v4
- `ImageManager::read($path)` → `ImageManager::decodePath($path)` (общий случай — `decode($input)`)
- `->toJpeg(quality: 85)->save($path)` → `->save($path, quality: 85)` — `save()` сам определяет формат по расширению файла и пробрасывает опции энкодеру (`JpegEncoder(quality: ...)`)

Проверено по `vendor/intervention/image/src/ImageManager.php` — метода `read()` в v4 физически нет.

### Урок
После мажорного апгрейда пакета — прогонять статанализ: `Call to an undefined method` ловит это до продакшена, даже если фича ещё ни разу не запускалась.

---

## 3. Filament 5: переезд классов между неймспейсами

**Дата:** 29.08.2026 · **Найдено:** runtime-фейтал (Alex, /admin/ads) + PHPStan

### Симптом
```
Class "App\Filament\Admin\Resources\Ads\Tables\IconColumn" not found
```
`IconColumn` использовался без `use` — PHP искал класс в неймспейсе таблицы.

Дальше PHPStan по `app/Filament/` вскрыл ещё пачку мёртвых ссылок — страницы Pipelines в админке были полностью нерабочими (`Class not found` на `Section::make()`).

### Куда переехало в Filament 5 (из v3/v4)
| Было (v3/v4) | Стало (v5) |
|---|---|
| `Filament\Forms\Components\Section` | `Filament\Schemas\Components\Section` |
| `Filament\Forms\Components\Component` | `Filament\Schemas\Components\Component` |
| `Filament\Forms\Get` / `Filament\Forms\Set` | `Filament\Schemas\Components\Utilities\Get` / `...Set` |
| `Filament\Tables\Actions\ViewAction` | `Filament\Actions\ViewAction` |
| `Filament\Tables\Actions\Action` | `Filament\Actions\Action` |
| `$action->form(fn (Form $f) => $f->schema([...]))` | `$action->schema([...])` — `form()` это deprecated-алиас |
| `->unique(ignoringRecord: true)` | `->unique(ignoreRecord: true)` |
| `TextColumn::uppercase()` | `->formatStateUsing(fn ($s) => mb_strtoupper($s))` |

Проверять классы можно в `vendor/filament/...` — tinker-сниппет: `class_exists('Filament\Forms\Get')` → `false`.

### Урок
1. `use`-строки после мажорного апгрейда Filament — первая линия проверки. PHP без автозагрузки падает только в рантайме на конкретной странице.
2. **PHPStan-конфиг, исключавший `app/Filament/*`, прятал все эти баги.** Исключение снято — теперь покрытие `app/`, `routes/`, `config/` целиком. Не исключать целые директории: дешевле чинить предупреждения, чем ловить `Class not found` в проде.

---

## 4. `ARRAY_FILTER_USE_KEY` — фильтр null не работал в CSV-импорте

**Дата:** 29.08.2026 · **Найдено:** разбор диффа · **Файл:** `app/Pipelines/Adapters/CsvProductsImport.php`

### Симптом (скрытый, порча данных)
```php
// было
$ad->update(array_filter($data, fn ($v) => $v !== null, ARRAY_FILTER_USE_KEY));
```
С флагом `ARRAY_FILTER_USE_KEY` колбэк получает **ключ** массива (строки `'title'`, `'price'`, …), а не значение. `$key !== null` всегда true → `array_filter` возвращал массив без изменений. Все `null`-поля из `$data` уходили в `update()` — **пустые ячейки CSV затирали существующие данные товара** при обновлении по SKU.

### Фикс
```php
$ad->update(array_filter($data, fn ($v) => $v !== null));
```

### Урок
`ARRAY_FILTER_USE_KEY` / `ARRAY_FILTER_USE_BOTH` меняют, ЧТО попадает в колбэк. Прочитай флаг дважды, прежде чем совмещать его с колбэком, написанным под значения.

---

## 6. Dot-колонка Filament на не-relation методе: `getRelated() on null`

**Дата:** 29.08.2026 · **Симптом (runtime):** `/admin/pipelines` → `Call to a member function getRelated() on null` (HasCellState.php:456)

### Причина
`TextColumn::make('lastRun.status')` — Filament в dot-нотации проверяет первую часть через `Model::isRelation()`, а тот возвращает true для **ЛЮБОГО метода модели** (проверка `method_exists`). Наш `lastRun()` был обычным методом `?PipelineLog` (лог из `logs()->first()`), не relation'ом. Filament звал `lastRun()->getRelated()` → на пайплайне без запусков это `null->getRelated()` → фейтал. Колонка не могла работать ни при каких данных.

### Фикс
```php
/** @return HasOne<PipelineLog, $this> */
public function lastRun(): HasOne
{
    return $this->hasOne(PipelineLog::class)->latestOfMany();
}
```
Настоящий relation — dot-нотация `lastRun.status` / `lastRun.created_at` работает, без запусков колонка показывает placeholder, plus появился eager-load `Pipeline::with('lastRun')`.

### Урок
1. Публичные методы моделей, не являющиеся relations, — мина: Eloquent `isRelation()` считает методом-relation всё, что `method_exists`. Имя метода без глагола — сразу думать «а не relation ли это должен быть».
2. Дот-нотация в Filament-колонках требует настоящих relations. «Хочу последний из» — это `hasOne()->latestOfMany()`, не `hasMany()->first()` обёрнутый в метод.

---

## 5. Прочее, пойманное тем же прогоном (29.08.2026)

- `PipelineService::run()` — мёртвый `$details = []` (никогда не заполнялся, ternary always false), `fresh()` мог вернуть `null` → заменён на `refresh()`.
- `VerifyEmailController` — `Verified`-ивент требует `MustVerifyEmail`; `User` теперь имплементит интерфейс, null-guard на `$request->user()`.
- Eloquent generics (`@return HasMany<X, $this>` / `BelongsTo<X, $this>`) проставлены на все 37 релейшенов — снимает ворох ложных `Model::$property` и включает автокомплит в IDE.

---

## PHPStan — как запускаем

```bash
composer stan        # alias: vendor/bin/phpstan analyse --no-progress
```

- Larastan 3, level 5, покрытие: `app/`, `routes/`, `config/` — **без исключений**
- Конфиг: `phpstan.neon`
- Состояние: **0 ошибок**, 102 теста зелёные

Правило: перед пушем — `composer test && composer stan`.