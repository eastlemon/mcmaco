# mcma.co

Интернет-магазин физических товаров с доставкой по России.

## Стек

- **Backend:** Laravel 13, PHP 8.4
- **Admin:** Filament 5 (Livewire 4)
- **DB:** MySQL 8 + Redis
- **Frontend:** Tailwind CSS + Livewire
- **Платежи:** ЮKassa
- **Auth:** Breeze
- **Тесты:** PHPUnit (45 тестов)

## Возможности

- Каталог товаров с Livewire-фильтрами (поиск, категории, цена, состояние, город)
- Карточка товара с schema.org Product
- Корзина (session-based, для гостей и пользователей)
- Оформление заказа с доставкой (СДЭК, Почта России, курьер, самовывоз)
- Оплата через ЮKassa (redirect + webhook)
- Админ-панель Filament: заказы, платежи, товары, категории
- Чаты, избранное

## Установка

```bash
git clone git@github.com:eastlemon/mcmaco.git
cd mcmaco
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan test
```

## Структура

```
app/
├── Filament/Admin/Resources/   # Orders, Payments, Ads, Categories, ...
├── Http/Controllers/           # AdController, CheckoutController, PaymentController
├── Livewire/                   # ProductBrowser, CartDropdown, CartPage
├── Models/                     # Ad, Order, Payment, Cart, Category, ...
└── Services/                   # CartService, YooKassaService
```

## Roadmap

- ✅ Этап 1 — Витрина (storefront, schema.org, SEO)
- ✅ Этап 2 — Корзина и заказы
- ✅ Этап 3 — Платежи (ЮKassa)
- 🔜 Этап 4 — Доставка (трекинг, расчёт стоимости)
- 🔜 Этап 5 — Pipelines (импорт/экспорт, сторонние API)
- 🔜 Этап 6 — SEO и аналитика

Спецификация: [SPEC.md](SPEC.md)

## License

MIT
