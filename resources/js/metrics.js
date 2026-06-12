/**
 * Laravel Metric Cards – standalone browser bundle.
 *
 * This file is bundled (together with Chart.js and the Colors plugin) into
 * `dist/metrics.js` via the package build (see package.json). The resulting
 * bundle is shipped with the package and published to
 * `public/vendor/laravel-metrics/metrics.js` so host applications do not need
 * to install or import Chart.js themselves.
 *
 * Responsibilities:
 *  - Expose `window.LaravelMetrics.loadChart()` which lazily prepares Chart.js
 *    exactly once and resolves with the constructor.
 *  - Prefer an already provided `window.Chart` (backward compatibility) and
 *    never overwrite it.
 *  - Register the Alpine components `metricPieChart` and `metricTrendChart`
 *    so the Blade views only have to pass data.
 */

import Chart from 'chart.js/auto'
import { Colors } from 'chart.js'

const DEFAULT_BACKGROUND_COLOR = '#6c5cff'
const DEFAULT_BORDER_COLOR = '#6c5cff'
const DEFAULT_FONT_COLOR = '#666'

/**
 * Read the chart configuration provided by the host application through the
 * `_assets.blade.php` view (Laravel config) or set manually before the bundle
 * is loaded.
 *
 * @returns {object}
 */
function getChartConfig() {
  return (window.LaravelMetrics && window.LaravelMetrics.config && window.LaravelMetrics.config.chart) || {}
}

/**
 * Clone an (possibly reactive Alpine/Livewire proxy) array into a plain array.
 *
 * Passing reactive proxy arrays/objects straight into Chart.js can make its
 * options/data resolver recurse indefinitely ("RangeError: Maximum call stack
 * size exceeded"). Every value handed to `new Chart(...)` / `chart.update()`
 * must therefore be a plain, non-reactive array/object.
 *
 * @param {*} value
 * @returns {Array}
 */
function plainArray(value) {
  return Array.isArray(value) ? Array.from(value) : []
}

/**
 * Build a deterministic list of `count` colors from the configured package
 * palette (`dataset_colors`). Returns an empty array when no palette is
 * configured so callers can fall back to Chart.js defaults / the Colors plugin.
 *
 * @param {number} count
 * @returns {string[]}
 */
function getDatasetColors(count) {
  const colors = getChartConfig().datasetColors || []

  if (!Array.isArray(colors) || colors.length === 0) {
    return []
  }

  return Array.from({ length: count }, (_, index) => colors[index % colors.length])
}

/**
 * Prepare the bundled Chart.js constructor (apply defaults, register plugins)
 * exactly once. The returned promise is cached on `window.LaravelMetrics`.
 *
 * @returns {Promise<typeof Chart>}
 */
function prepareChart() {
  // Backward compatibility: if the host application already provided its own
  // Chart instance we use it and do not touch its configuration.
  if (window.Chart) {
    return Promise.resolve(window.Chart)
  }

  const chartConfig = getChartConfig()
  const defaults = chartConfig.defaults || {}

  // Only the simple scalar defaults are safe to set globally. The `colors`
  // plugin options are intentionally NOT written to
  // `Chart.defaults.plugins.colors`, because doing so can trigger a recursive
  // defaults/fallback resolution inside Chart.js' options resolver and lead to
  // a "RangeError: Maximum call stack size exceeded". Instead they are applied
  // per chart via `withPackagePluginOptions()`.
  Chart.defaults.backgroundColor = defaults.backgroundColor || DEFAULT_BACKGROUND_COLOR
  Chart.defaults.borderColor = defaults.borderColor || DEFAULT_BORDER_COLOR
  Chart.defaults.color = defaults.color || DEFAULT_FONT_COLOR

  Chart.register(Colors)

  // Expose globally for backward compatibility with views/host code that may
  // still reference `window.Chart` directly.
  window.Chart = Chart

  return Promise.resolve(Chart)
}

/**
 * Build the `colors` plugin options from the package configuration. These are
 * meant to be passed per chart (inside `options.plugins.colors`) instead of
 * being written to the global `Chart.defaults.plugins.colors`.
 *
 * @returns {{enabled: boolean, forceOverride: boolean}}
 */
function getColorsPluginOptions() {
  const colorsPlugin = getChartConfig().colorsPlugin || {}

  return {
    enabled: colorsPlugin.enabled ?? true,
    forceOverride: colorsPlugin.forceOverride ?? false,
  }
}

/**
 * Wrap a chart `options` object with the package-managed plugin options
 * (currently the `colors` plugin). Existing per-chart plugin options take
 * precedence over the package defaults.
 *
 * @param {object} [options]
 * @returns {object}
 */
function withPackagePluginOptions(options = {}) {
  const plugins = options.plugins || {}

  return {
    ...options,
    plugins: {
      ...plugins,
      colors: {
        ...getColorsPluginOptions(),
        ...(plugins.colors || {}),
      },
    },
  }
}

window.LaravelMetrics = window.LaravelMetrics || {}
const Metrics = window.LaravelMetrics

Metrics.getDatasetColors = getDatasetColors
Metrics.getColorsPluginOptions = getColorsPluginOptions
Metrics.withPackagePluginOptions = withPackagePluginOptions

/**
 * Lazily resolve the Chart.js constructor. Subsequent calls reuse the same
 * promise so Chart.js is only prepared/registered once.
 *
 * @returns {Promise<typeof Chart>}
 */
Metrics.loadChart = function loadChart() {
  if (!Metrics.chartReady) {
    Metrics.chartReady = prepareChart()
  }

  return Metrics.chartReady
}

/**
 * Alpine component for the Pie / Doughnut metric.
 *
 * @param {object} config
 * @param {string[]} config.labels
 * @param {number[]} config.values
 * @param {string[]} config.colors
 * @param {number[]} config.invisible
 * @param {boolean} config.doughnut
 */
function metricPieChart(config) {
  // The Chart.js instance is intentionally kept in a closure variable instead
  // of on the Alpine component. Storing it as a reactive Alpine property turns
  // the whole chart (its data/options object graph) into a reactive proxy,
  // which makes Chart.js' options/data resolver recurse on legend clicks
  // ("RangeError: Maximum call stack size exceeded" / "layout.configure: item
  // undefined"). The closure keeps the instance fully non-reactive.
  let chart = null

  return {
    labels: config.labels,
    values: config.values,
    colors: config.colors,
    invisible: config.invisible,
    doughnut: config.doughnut,

    async init() {
      const Chart = await Metrics.loadChart()

      // Clone all reactive Alpine/Livewire proxy data into plain arrays before
      // handing them to Chart.js to avoid recursive options/data resolution.
      const labels = plainArray(this.labels)
      const values = plainArray(this.values)

      // Metric-specific colors (passed from PHP) always take precedence. Only
      // when none are configured do we fall back to the package palette.
      const configuredColors = plainArray(this.colors)
      const fallbackColors = Metrics.getDatasetColors(values.length)
      const colors = configuredColors.length ? configuredColors : fallbackColors

      chart = new Chart(this.$refs.canvas.getContext('2d'), {
        type: 'pie',
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: plainArray(colors),
              borderColor: plainArray(colors),
            },
          ],
        },
        options: withPackagePluginOptions({
          cutout: this.doughnut ? '50%' : 0,
          maintainAspectRatio: false,
          animation: {
            duration: 400,
          },
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false },
          },
          scales: {
            x: { display: false },
            y: { display: false },
          },
        }),
      })

      this.$watch('values', () => this.updateChart())
      this.$watch('invisible', (value) => {
        // Defer to a microtask so we do not run Chart.js inside the Alpine
        // reactive effect that triggered the watcher (which would otherwise
        // re-enter the reactive proxy and recurse).
        queueMicrotask(() => this.checkInvisible(value, true))
      })

      // Apply the initial visibility once the chart exists.
      this.checkInvisible(this.invisible, false)
    },

    destroy() {
      chart?.destroy()
      chart = null
    },

    updateChart() {
      if (!chart) {
        return
      }

      const labels = plainArray(this.labels)
      const values = plainArray(this.values)

      const configuredColors = plainArray(this.colors)
      const fallbackColors = Metrics.getDatasetColors(values.length)
      const colors = configuredColors.length ? configuredColors : fallbackColors

      chart.data.labels = labels
      chart.data.datasets[0].data = values
      chart.data.datasets[0].backgroundColor = plainArray(colors)
      chart.data.datasets[0].borderColor = plainArray(colors)
      chart.update()
    },

    checkInvisible(value, update = true) {
      if (!chart) {
        return
      }

      const invisible = plainArray(value)

      chart.data.datasets[0].data.forEach((_, index) => {
        const shouldBeInvisible = invisible.includes(index)
        const isVisible = chart.getDataVisibility(index)

        if (shouldBeInvisible && isVisible) {
          chart.toggleDataVisibility(index)
        }

        if (!shouldBeInvisible && !isVisible) {
          chart.toggleDataVisibility(index)
        }
      })

      if (update) {
        chart.update()
      }
    },

    toggle(key) {
      const arr = this.invisible
      const idx = arr.indexOf(key)

      if (idx !== -1) {
        arr.splice(idx, 1)
      } else {
        arr.push(key)
      }
    },

    isInvisible(key) {
      return this.invisible.indexOf(key) !== -1
    },
  }
}

/**
 * Alpine component for the Trend (line) metric.
 *
 * @param {object} config
 * @param {string[]} config.labels
 * @param {number[]} config.values
 */
function metricTrendChart(config) {
  // See `metricPieChart` for why the Chart.js instance is kept in a closure
  // variable instead of a reactive Alpine property.
  let chart = null

  return {
    labels: config.labels,
    values: config.values,

    async init() {
      const Chart = await Metrics.loadChart()

      // Clone all reactive Alpine/Livewire proxy data into plain arrays before
      // handing them to Chart.js to avoid recursive options/data resolution.
      const labels = plainArray(this.labels)
      const values = plainArray(this.values)

      // Apply the package palette to the (single) trend dataset unless it
      // already provides its own colors.
      const color = Metrics.getDatasetColors(1)[0]
      const dataset = { data: values }
      if (color) {
        dataset.borderColor = color
        dataset.backgroundColor = color
      }

      chart = new Chart(this.$refs.canvas.getContext('2d'), {
        type: 'line',
        data: {
          labels,
          datasets: [dataset],
        },
        options: withPackagePluginOptions({
          responsive: true,
          animation: {
            duration: 400,
          },
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
          },
          scales: {
            x: { display: false },
            y: { display: false },
          },
        }),
      })

      this.$watch('values', () => this.updateChart())
    },

    destroy() {
      chart?.destroy()
      chart = null
    },

    updateChart() {
      if (!chart) {
        return
      }

      chart.data.labels = plainArray(this.labels)
      chart.data.datasets[0].data = plainArray(this.values)
      chart.update()
    },
  }
}

Metrics.metricPieChart = metricPieChart
Metrics.metricTrendChart = metricTrendChart

/**
 * Register the Alpine components. The bundle is loaded as a classic script via
 * an `@once` directive inside the package views, i.e. before the (deferred,
 * module based) host bundle calls `Alpine.start()`. Therefore listening to
 * `alpine:init` is the reliable registration point. As a safety net we also
 * register immediately if Alpine is already available.
 */
function registerComponents(Alpine) {
  if (!Alpine || Alpine.__laravelMetricsRegistered) {
    return
  }

  Alpine.__laravelMetricsRegistered = true
  Alpine.data('metricPieChart', metricPieChart)
  Alpine.data('metricTrendChart', metricTrendChart)
}

document.addEventListener('alpine:init', () => registerComponents(window.Alpine))

if (window.Alpine) {
  registerComponents(window.Alpine)
}
