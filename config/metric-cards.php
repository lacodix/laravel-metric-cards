<?php

declare(strict_types=1);

return [
    'metrics_view_prefix' => 'lacodix-metrics::metrics.',

    'asset_stack' => 'scripts',

    'chart' => [
        // Global Chart.js fallback colors (Chart.defaults.*).
        'defaults' => [
            'background_color' => '#6c5cff',
            'border_color' => '#6c5cff',
            'font_color' => '#666',
        ],

        // Package palette used for pie segments and multiple trend datasets.
        'dataset_colors' => [
            '#6c5cff',
            '#22c55e',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#a855f7',
            '#84cc16',
        ],

        // Chart.js "Colors" plugin configuration.
        'colors_plugin' => [
            'enabled' => true,
            'force_override' => false,
        ],
    ],
];
