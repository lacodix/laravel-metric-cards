### DO NOT USE YET
Work in progress

## Assets

The package ships a pre-built, standalone browser bundle (`dist/metrics.js`) that
already includes Chart.js (incl. the `Colors` plugin). Host applications do **not**
need to install or import Chart.js themselves.

`dist/metrics.js` is committed to the repository and therefore part of every
release tag. When you change the source in `resources/js/metrics.js` you must
rebuild and commit the bundle:

```bash
npm ci
npm run build
git add dist/metrics.js
```

CI verifies via `git diff --exit-code dist/metrics.js` that the committed bundle
matches the source. Releases are only created when the bundle is up to date.

### Asset publishing

Publish the bundle to your application's `public` directory:

```bash
php artisan vendor:publish --tag=laravel-metrics-assets
```

The metric views automatically include the script exactly once per page, even
when several metrics are rendered.

### Config publishing

Publish the package configuration:

```bash
php artisan vendor:publish --tag=laravel-metric-cards-config
```

This creates `config/metric-cards.php`.

## Chart colors

Host applications can control the Chart.js colors entirely through
`config/metric-cards.php` — without editing or rebuilding `dist/metrics.js`:

```php
'chart' => [
    // Chart.js global fallback colors (Chart.defaults.*).
    'defaults' => [
        'background_color' => '#6c5cff',
        'border_color' => '#6c5cff',
        'font_color' => '#111827',
    ],

    // Package palette used for pie segments and multiple trend datasets.
    'dataset_colors' => [
        '#6c5cff',
        '#00c2a8',
        '#f59e0b',
        '#ef4444',
    ],

    // Chart.js "Colors" plugin configuration.
    'colors_plugin' => [
        'enabled' => true,
        'force_override' => false,
    ],
],
```

- `defaults` — the global Chart.js fallback colors (`Chart.defaults.backgroundColor`,
  `Chart.defaults.borderColor`, `Chart.defaults.color`).
- `dataset_colors` — the package palette used for pie segments and for multiple
  trend datasets. Metric-specific colors (e.g. the `$colors` array on a `Pie`
  metric) always take precedence; the palette is only used as a fallback.
- `colors_plugin` — configuration for the Chart.js `Colors` plugin. With
  `force_override = false` (default) the plugin does not overwrite colors that
  the package already set on a dataset.

These settings are passed from PHP to JavaScript via `window.LaravelMetrics.config`
in the package's `_assets.blade.php` view. The Laravel config is the primary
override source; any `window.LaravelMetrics.config` set manually before the
bundle loads only complements it.
