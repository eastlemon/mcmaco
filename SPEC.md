# mcma.co — Интернет-магазин

## Концепция

Витрина-магазин для продажи физических товаров с доставкой. Управление товарами через Filament. Корзина, заказы, оплата через ЮKassa. Livewire-фильтры каталога. SEO. Pipelines для сторонних API.

- **Домен**: `mcmaco.ru`
- **Repo**: `git@github.com:eastlemon/mcmaco.git`, branch `main`
- **Dev**: `/var/www/mcmaco` на vdska

## Стек

- **Backend**: Laravel 13 + PHP 8.4
- **Admin**: Filament 5 (Livewire 4)
- **DB**: MySQL 8 + Redis
- **Frontend**: Tailwind CSS + Livewire
- **Платежи**: ЮKassa (`yoomoney/yookassa-sdk-php`)
- **Тесты**: PHPUnit (45 тестов / 117 assertions)
- **Auth**: Breeze

## Что уже реализовано

### Витрина (Этап 1 ✅)
- Модель `Ad` (используется как товар, не «объявление») — slug, sku, stock, is_featured, weight, dimensions, SEO-поля
- Главная страница: hero, категории, популярные товары (is_featured), сетка всех товаров
- Карточка товара `/listing/{slug}`: галерея, цена, наличие, schema.org Product JSON-LD, похожие товары
- `Category` — древовидная (parent/children), SEO-поля
- Sitemap command + robots.txt

### Фильтры каталога (✅)
- **Livewire `ProductBrowser`** — живая фильтрация с URL-синхронизацией
- Фильтры: поиск (title/description/sku), категория (с подкатегориями), цена от/до, состояние, город, в наличии, хиты
- Сортировка: новые / цена ↑ / цена ↓ / популярные
- Mobile: раскрывающийся сайдбар с бейджем активных фильтров
- Сброс одним кликом

### Корзина и заказы (Этап 2 ✅)
- `Cart` + `CartItem` — session-based, guest carts
- `CartService` — getOrCreate, clear
- `CheckoutController` — оформление (контакты, адрес, способ доставки)
- Способы доставки: самовывоз / СДЭК / Почта России / курьер (фикс. цена)
- `Order` + `OrderItem` — snapshot цены/названия, order_number (auto-gen)
- Статусы: new → confirmed → paid → processing → shipped → delivered → done / cancelled
- Filament: OrderResource (просмотр, управление статусами)

### Платежи (Этап 3 ✅)
- `Payment` модель — provider, provider_payment_id, status, amount, confirmation_url, payload (JSON), paid_at
- `YooKassaService` — createPayment, checkPaymentStatus, refund, processWebhook
- `YooKassaHttpClient` — HTTP-клиент с idempotence key
- `PaymentController` — `/order/{order}/pay` (redirect), `/order/{order}/success`, `/payments/yookassa/webhook`
- Webhook: `payment.succeeded` → Order::STATUS_PAID + paid_at (идемпотентно)
- Filament: PaymentResource — список, просмотр, кнопки «Проверить статус» и «Возврат»
- config: `config/payments.php` (shop_id, secret_key, test_mode)
- `.env`: `YOOKASSA_ENABLED`, `YOOKASSA_SHOP_ID`, `YOOKASSA_SECRET_KEY`

### Прочее (из базовой доски объявлений)
- Чат между покупателем и продавцом (Chat, Message)
- Избранное (Favorite)
- Breeze auth (login/register/profile)
- Навигация: @auth/@guest (починено — не падает для гостей)

## Модели

| Модель | Назначение |
|--------|-----------|
| `User` | Пользователь (customer/seller/admin) |
| `Category` | Категория товара (дерево, SEO-поля) |
| `Ad` | Товар (slug, sku, price, stock, condition, is_featured, SEO) |
| `AdImage` | Изображение товара |
| `Cart` / `CartItem` | Корзина (session-based) |
| `Order` / `OrderItem` | Заказ + позиции (snapshot) |
| `Payment` | Платёж (ЮKassa) |
| `Chat` / `Message` | Чат между покупателем и продавцом |
| `Favorite` | Избранное |
| `Report` | Жалоба |

## Маршруты (витрина)

| Route | Описание |
|-------|----------|
| `/` | Главная: hero + категории + популярные + каталог с фильтрами |
| `/listing/{slug}` | Карточка товара (schema.org Product) |
| `/cart` | Корзина (Livewire) |
| `/checkout` | Оформление заказа |
| `/order/{order}` | Статус заказа + кнопка оплаты |
| `/order/{order}/pay` | Инициация платежа ЮKassa |
| `/order/{order}/success` | Возврат после оплаты |
| `/payments/yookassa/webhook` | Webhook от ЮKassa |

## Filament Resources

Orders, Payments, Ads, Categories, Chats, Messages, Reports, Users

## Этапы

### ✅ Этап 1 — Рефакторинг базы
- Расширение ads: slug, sku, stock, is_featured, SEO-поля, weight, dimensions
- Главная витрина (популярные + новинки)
- Карточка товара с schema.org
- Базовый SEO (meta, sitemap, robots)

### ✅ Этап 2 — Корзина и заказы
- Cart + CartItem (session-based, Livewire)
- Checkout (контакты, адрес, доставка)
- Order + OrderItem модели
- Filament: OrderResource

### ✅ Этап 3 — Платежи
- ЮKassa integration (create payment, redirect, webhook)
- Payment модель, статусы
- Filament: PaymentResource, возвраты
- Тестовый режим через env

### 🔜 Этап 4 — Доставка
- Справочник способов доставки (СДЭК, Почта, самовывоз)
- Расчёт стоимости (пока фиксированный/по зоне)
- Трекинг-номера
- Уведомления покупателю

### 🔜 Этап 5 — Pipelines (сторонние API)
- Pipeline + PipelineLog модели
- AdapterRegistry (как в LeadFlow)
- Импорт товаров (CSV/XML)
- Синхронизация цен/наличия
- Экспорт заказов
- Filament: PipelineResource, логи, ручной запуск

### 🔜 Этап 6 — SEO и аналитика
- Sitemap.xml (автогенерация, cron)
- schema.org Product + BreadcrumbList
- Open Graph, Twitter Cards
- Meta per listing/category
- Yandex.Metrika / Google Analytics

## Не в скоупе (пока)
- Мульти-вендор (несколько продавцов)
- Складской учёт
- Мобильное приложение
- Платная подписка/промо

## Tech notes
- `category_id` nullable на ads (с 24.07.2026)
- `doctrine/dbal` установлен для change()-миграций
- Layout использует `@yield('content')` (не `{{ $slot }}`)
