@if(config('analytics.metrika.enabled') && config('analytics.metrika.counter_id'))
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function(m,e,t,r,i,k,a){
    m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
})(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

ym({{ config('analytics.metrika.counter_id') }}, "init", {
    clickmap: {{ config('analytics.metrika.clickmap') ? 'true' : 'false' }},
    ecommerce: {{ config('analytics.metrika.ecommerce') ? 'dataLayer' : 'false' }},
    accurateTrackBounce: {{ config('analytics.metrika.accurate_track_bounce') ? 'true' : 'false' }}@if(config('analytics.metrika.webvisor')),
    webvisor: true@endif
});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/{{ config('analytics.metrika.counter_id') }}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
@endif
