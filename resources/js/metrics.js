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

const BRAND_COLOR = '#6c5cff'

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

  Chart.defaults.backgroundColor = BRAND_COLOR
  Chart.defaults.borderColor = BRAND_COLOR
  Chart.register(Colors)

  // Expose globally for backward compatibility with views/host code that may
  // still reference `window.Chart` directly.
  window.Chart = Chart

  return Promise.resolve(Chart)
}

const LaravelMetrics = (window.LaravelMetrics = window.LaravelMetrics || {})

/**
 * Lazily resolve the Chart.js constructor. Subsequent calls reuse the same
 * promise so Chart.js is only prepared/registered once.
 *
 * @returns {Promise<typeof Chart>}
 */
LaravelMetrics.loadChart = function loadChart() {
  if (!LaravelMetrics.chartReady) {
    LaravelMetrics.chartReady = prepareChart()
  }

  return LaravelMetrics.chartReady
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
  return {
    chart: null,
    labels: config.labels,
    values: config.values,
    colors: config.colors,
    invisible: config.invisible,
    doughnut: config.doughnut,

    async init() {
      const Chart = await window.LaravelMetrics.loadChart()

      this.chart = new Chart(this.$refs.canvas.getContext('2d'), {
        type: 'pie',
        data: {
          labels: this.labels.slice(),
          datasets: [
            {
              data: this.values.slice(),
            },
          ],
        },
        options: {
          cutout: this.doughnut ? '50%' : 0,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { enabled: false },
          },
          scales: {
            x: { display: false },
            y: { display: false },
          },
          elements: {
            arc: {
              backgroundColor: this.colors,
            },
          },
        },
      })

      this.$watch('values', () => this.updateChart())
      this.$watch('invisible', (values) => this.checkInvisible(values))

      this.checkInvisible(this.invisible)
    },

    destroy() {
      if (this.chart) {
        this.chart.destroy()
        this.chart = null
      }
    },

    updateChart() {
      if (!this.chart) {
        return
      }

      this.chart.data.labels = this.labels.slice()
      this.chart.data.datasets[0].data = this.values.slice()
      this.chart.update()
    },

    checkInvisible(invisible) {
      if (!this.chart) {
        return
      }

      const dsMeta = this.chart.getDatasetMeta(0)

      dsMeta.data.forEach((arc, idx) => {
        const shouldBeHidden = invisible.includes(idx)
        const isCurrentlyVisible = this.chart.getDataVisibility(idx)

        if (shouldBeHidden && isCurrentlyVisible) {
          this.chart.toggleDataVisibility(idx)
        } else if (!shouldBeHidden && !isCurrentlyVisible) {
          this.chart.toggleDataVisibility(idx)
        }
      })

      this.chart.update()
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
  return {
    chart: null,
    labels: config.labels,
    values: config.values,

    async init() {
      const Chart = await window.LaravelMetrics.loadChart()

      this.chart = new Chart(this.$refs.canvas.getContext('2d'), {
        type: 'line',
        data: {
          labels: this.labels,
          datasets: [
            {
              data: this.values,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
          },
          scales: {
            x: { display: false },
            y: { display: false },
          },
        },
      })

      this.$watch('values', () => this.updateChart())
    },

    destroy() {
      if (this.chart) {
        this.chart.destroy()
        this.chart = null
      }
    },

    updateChart() {
      if (!this.chart) {
        return
      }

      this.chart.data.labels = this.labels
      this.chart.data.datasets[0].data = this.values
      this.chart.update()
    },
  }
}

LaravelMetrics.metricPieChart = metricPieChart
LaravelMetrics.metricTrendChart = metricTrendChart

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
