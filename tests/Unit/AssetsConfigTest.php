<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

function renderAssets(): string
{
    return Blade::render(
        "@include('lacodix-metrics::metrics._assets')\n@stack('scripts')"
    );
}

test('assets view exposes the chart config from laravel config to javascript', function () {
    config()->set('metric-cards.chart.defaults.background_color', '#123456');
    config()->set('metric-cards.chart.defaults.border_color', '#abcdef');
    config()->set('metric-cards.chart.defaults.font_color', '#0f0f0f');
    config()->set('metric-cards.chart.dataset_colors', ['#111111', '#222222']);
    config()->set('metric-cards.chart.colors_plugin.enabled', false);
    config()->set('metric-cards.chart.colors_plugin.force_override', true);

    $html = renderAssets();

    expect($html)
        ->toContain('window.LaravelMetrics = window.LaravelMetrics || {}')
        ->toContain('#123456')
        ->toContain('#abcdef')
        ->toContain('#0f0f0f')
        ->toContain('#111111')
        ->toContain('#222222')
        ->toContain('forceOverride');
});

test('assets view includes the metrics script exactly once', function () {
    $html = renderAssets();

    expect(substr_count($html, 'metrics.js'))->toBe(1);
});
