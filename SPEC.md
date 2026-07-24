# mcma.co — Интернет-магазин

## Концепция

Витрина-магазин для продажи физических товаров с доставкой. Управление товарами как записями в блоге через Filament. Корзина, заказы, оплата через ЮKassa. SEO-инструменты. Готовность к интеграции сторонних API (pipelines).

## Текущее состояние репозитория

В репо уже есть базовая доска объявлений (Avito-style):
- Модели: Ad, Category, AdImage, Chat, Message, Favorite, Report, User
- Filament admin: Ads, Categories, Chats, Messages, Reports, Users
 Витрина: список + карточка объявления, фильтры, поиск
- Чат между покупателем и продавцом
- Избранное
- Breeze auth

**Что нужно добавить/переделать:**
- Ad → Listing (переход от «объявления» к «товару магазина»)
- Корзина + оформление заказа
- Оплата ЮKassa
- Быстрый заказ в 1 клик (по телефону)
- Витрина: популярные + новые
- SEO: meta, sitemap, schema.org
- Pipelines для сторонних API
- Доставка (адрес, трекинг)

## Стек

- **Backend**: Laravel 13 + PHP 8.4
- **Admin**: Filament 5 (Livewire 4)
- **DB**: MySQL 8 + Redis
- **Frontend**: Tailwind CSS + Livewire (витрина, корзина)
- **Платежи**: ЮKassa (YooMoney API)
- **Search**: MySQL FULLTEXT (позже Meilisearch)
- **Тесты**: Pest 4

## Модели (target)

### Существующие (адаптировать)
- **User** — +role (customer/seller/admin), +phone
- **Category** — +meta_title, +meta_description (SEO)
- **Listing** (бывший Ad) — +slug (unique), +sku, +stock, +is_featured, +meta_title, +meta_description, +weight, +dimensions

### Новые
- **Cart** — session-based, user_id nullable (guest carts)
- **CartItem** — cart_id, listing_id, qty
- **Order** — user_id, status, total, customer_name, phone, email, address, delivery_method, tracking_number, paid_at
- **OrderItem** — order_id, listing_id, title_snapshot, price_snapshot, qty
- **Payment** — order_id, provider, provider_payment_id, status, amount
- **Pipeline** — name, provider, config (JSON), is_active (для сторонних API)
- **PipelineLog** — pipeline_id, action, status, payload, created_at

## Витрина

### Страницы
| Route | Описание |
|-------|----------|
| `/` | Главная: популярные + новые товары, категории |
| `/category/{slug}` | Товары категории с фильтрами |
| `/listings` | Все товары (с фильтрами, сортировкой) |
| `/listing/{slug}` | Карточка товара (schema.org Product) |
| `/cart` | Корзина (Livewire) |
| `/checkout` | Оформление заказа |
| `/order/{order}` | Статус заказа |
| `/search?q=` | Поиск |

### Главная витрина
- Слайдер/сетка категорий
- «Популярные» — is_featured = true + топ по views/orders
- «Новинки» — последние созданные, status = active, stock > 0

### Карточка товара
- Галерея изображений
- Цена, наличие, SKU
- Кнопка «В корзину» + «Купить в 1 клик» (модалка с телефоном)
- schema.org Product JSON-LD (name, price, availability, images, rating)
- Похожие товары из категории

## Корзина и заказ

### Корзина
- Session-based, без обязательной регистрации
- Livewire компонент: +/− qty, удалить, subtotal
- При логине — привязка к user_id, мёрдж guest cart

### Оформление заказа
1. Контактные данные (имя, телефон, email)
2. Адрес доставки
3. Способ доставки (СДЭК, Почта России, самовывоз — пока справочник)
4. Комментарий
5. Оплата: ЮKassa (redirect → confirmation → webhook)

### Быстрый заказ (1 клик)
- Модалка на карточке товара
- Только телефон (обязательно) + имя (опционально)
- Создаёт Order со статусом `new`, без корзины
- Админ видит в Filament, перезванивает для уточнения

### Статусы заказа
`new` → `confirmed` → `paid` → `processing` → `shipped` → `delivered` → `done`
`new` → `cancelled`

## Оплата (ЮKassa)

- Создание платежа через YooKassa API (redirect flow)
- Webhook `payment.succeeded` → Order → `paid`
- Возвраты через Filament admin
- Тестовый режим через env

## SEO

- **Sitemap**: `php artisan sitemap:generate` — категории + товары + статические страницы
- **Meta**: per listing/category — title, description, og-tags
- **Schema.org**: Product (price, availability, images), BreadcrumbList
- **robots.txt**: allow all, sitemap link
- **Friendly URLs**: `/category/{slug}`, `/listing/{slug}`
- **Canonical**: на карточке товара

## Pipelines (сторонние API)

Архитектура как в LeadFlow — адаптеры с конфигом:

- **Импорт товаров**: CSV/XML/API → Listings (с маппингом полей)
- **Синхронизация цен/наличия**: внешний API → обновление stock/price
- **Экспорт заказов**: новый заказ → внешний CRM/ERP
- **Маркетплейс-экспорт**: товары → Яндекс.Маркет, Avito, VK

Каждый pipeline: config (JSON), provider, is_active. Jobs на очереди.

## Этапы

### Этап 1 — Рефакторинг базы (текущий → магазин)
- [ ] Миграция: переименование/расширение ads → listings (slug, sku, stock, is_featured, SEO)
- [ ] Обновить модели, контроллеры, views
- [ ] Главная витрина (популярные + новинки)
- [ ] Карточка товара с schema.org
- [ ] Базовый SEO (meta, sitemap, robots)

### Этап 2 — Корзина и заказы
- [ ] Cart + CartItem (session-based, Livewire)
- [ ] Checkout (контакты, адрес, доставка)
- [ ] Быстрый заказ в 1 клик
- [ ] Order + OrderItem модели
- [ ] Filament: OrderResource, управление статусами

### Этап 3 — Платежи
- [x] ЮKassa integration (create payment, redirect, webhook)
- [x] Payment модель, статусы
- [x] Filament: управление платежами, возвраты
- [x] Тестовый режим

### Этап 4 — Доставка
- [ ] Справочник способов доставки (СДЭК, Почта, самовывоз)
- [ ] Расчёт стоимости (пока фиксированный/по зоне)
- [ ] Трекинг-номера
- [ ] Уведомления покупателю

### Этап 5 — Pipelines (сторонние API)
- [ ] Pipeline + PipelineLog модели
- [ ] AdapterRegistry (как в LeadFlow)
- [ ] Импорт товаров (CSV/XML)
- [ ] Синхронизация цен/наличия
- [ ] Экспорт заказов
- [ ] Filament: PipelineResource, логи, ручной запуск

### Этап 6 — SEO и аналитика
- [ ] Sitemap.xml (автогенерация, cron)
- [ ] schema.org Product + BreadcrumbList
- [ ] Open Graph, Twitter Cards
- [ ] Meta per listing/category
- [ ] microdata breadcrumbs
- [ ] Yandex.Metrika / Google Analytics

## Не в скоупе (пока)
- Мульти-вендор (несколько продавцов) — пока один продавец (Alex)
- Складской учёт (позже)
- Мобильное приложение
- Платная подписка/промо
