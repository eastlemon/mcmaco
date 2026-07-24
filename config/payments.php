<?php

return [

    /*
    |--------------------------------------------------------------------------
    | YooKassa (YooMoney) Payment Provider
    |--------------------------------------------------------------------------
    */

    'enabled' => env('YOOKASSA_ENABLED', false),

    /*
    * Shop ID from YooKassa dashboard
    */
    'shop_id' => env('YOOKASSA_SHOP_ID'),

    /*
    * Secret API key from YooKassa dashboard
    */
    'secret_key' => env('YOOKASSA_SECRET_KEY'),

    /*
    * Test mode (use fake money)
    */
    'test_mode' => env('YOOKASSA_TEST_MODE', true),

    /*
    * Default currency
    */
    'currency' => 'RUB',

    /*
    * Confirmation flow: 'redirect' (embedded/redirect) or 'external'
    */
    'confirmation' => 'redirect',

    /*
    * Webhook URL path (relative to APP_URL)
    */
    'webhook_url' => '/payments/yookassa/webhook',

    /*
    * Description template for payments
    */
    'description_template' => 'Заказ {order_number} на {site}',

];
