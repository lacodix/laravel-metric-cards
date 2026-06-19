<script>
    window.LaravelMetrics = window.LaravelMetrics || {};
    window.LaravelMetrics.config = {
        ...(window.LaravelMetrics.config || {}),
        chart: {
            ...((window.LaravelMetrics.config || {}).chart || {}),
            defaults: {
                ...(((window.LaravelMetrics.config || {}).chart || {}).defaults || {}),
                backgroundColor: @js(config('metric-cards.chart.defaults.background_color', '#6c5cff')),
                borderColor: @js(config('metric-cards.chart.defaults.border_color', '#6c5cff')),
                color: @js(config('metric-cards.chart.defaults.font_color', '#666')),
            },
            datasetColors: @js(config('metric-cards.chart.dataset_colors', [])),
            colorsPlugin: {
                enabled: @js(config('metric-cards.chart.colors_plugin.enabled', true)),
                forceOverride: @js(config('metric-cards.chart.colors_plugin.force_override', false)),
            },
        },
    };
</script>

<script src="{{ \Lacodix\LaravelMetricCards\LaravelMetricCardsServiceProvider::assetUrl() }}"></script>
