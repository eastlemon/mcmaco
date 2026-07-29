@php
    $measurementId = config('analytics.ga4.measurement_id');
@endphp
@if(config('analytics.ga4.enabled') && $measurementId)
<!-- Google Analytics (GA4) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $measurementId }}', {
        'currency': 'RUB',
        'country': 'RU'
    });
</script>
<!-- /Google Analytics (GA4) -->
@endif
