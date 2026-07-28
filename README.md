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

    // How colors are derived once the palette is exhausted:
    // 'hue' (default), 'lightness' or 'repeat'.
    'color_derivation' => 'hue',

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
  trend datasets. A metric may override it for its own card by filling the
  `$colors` array on the `Pie` metric; left empty (the default) it follows this
  palette.
- `color_derivation` — how a chart colors segments once the palette is
  exhausted. The number of pie segments usually comes from data, so a palette
  can always be too short. The palette itself is never touched: the first
  segments of a chart are the palette in its configured order, derivation only
  starts afterwards.
  - `hue` (default) rotates the hue by the golden angle per palette run — the
    widest separation, at the price of leaving the color family. Greys, black
    and white have no hue to rotate, so those are varied by brightness instead.
  - `lightness` keeps the hue and varies the brightness — stays inside the
    color family, distinguishes less clearly, and because brightness is bounded
    a heavily recycled palette eventually repeats.
  - `repeat` cycles the palette unchanged.

  A single metric can deviate by setting `protected ?ColorDerivation $colorDerivation`.

  **Derivation only understands hex colors** (`#rgb`, `#rgba`, `#rrggbb`,
  `#rrggbbaa`; transparency is preserved). A palette of `rgb()`, `hsl()` or CSS
  variables is cycled unchanged — those colors repeat once the palette is
  exhausted. Use hex values in the palette if a chart may have more segments
  than the palette has colors.

  An empty palette falls back to the package's built-in one, so legend and
  canvas always agree on a color.
- `colors_plugin` — configuration for the Chart.js `Colors` plugin. With
  `force_override = false` (default) the plugin does not overwrite colors that
  the package already set on a dataset.

These settings are passed from PHP to JavaScript via `window.LaravelMetrics.config`
in the package's `_assets.blade.php` view. The Laravel config is the primary
override source; any `window.LaravelMetrics.config` set manually before the
bundle loads only complements it.

### Upgrading pie metrics

Two breaking changes come with the per-segment color resolution:

- The pie view renders `$segmentColors` instead of `$colors`. **A published or
  overridden copy of the old view has to be re-published**, otherwise it reads
  `$colors`, which is now the (possibly empty) palette rather than one color
  per segment.
- `Pie::$colors` no longer carries hard-coded colors, so pie charts follow
  `dataset_colors` like every other chart. A metric that wants its own palette
  still declares `public array $colors = [...]`.
