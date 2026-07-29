@push('head_extra')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'mcmaco',
            'url' => config('app.url'),
            'description' => 'Интернет-магазин товаров с доставкой по России',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'RU',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'mcmaco',
            'url' => config('app.url'),
            'inLanguage' => 'ru-RU',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => config('app.url') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
