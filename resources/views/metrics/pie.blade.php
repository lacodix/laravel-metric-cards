<div
    class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-4 relative"
    data-metric-name="{{ $this->name() }}"
>
    <div class="flex justify-between mb-4">
        <div class="font-bold">{!! $this->title() !!}</div>
        <div class="text-xs text-gray-600">{!! $this->total() !!}</div>
    </div>

    <ul>
        @foreach($values as $key => $value)
            <li class="text-xs text-gray-600 mb-1">
                <span class="inline-block rounded-full w-2 h-2 mr-2" style="background-color: {{ $colors[$key] }}"></span>
                {!! $labels[$key] ?? '' !!}
            </li>
        @endforeach
    </ul>

    <div
        class="overflow-hidden absolute bottom-0 right-0 p-2 h-2/3 w-1/3"
        x-data="{
            labels: @entangle('labels').live,
            values: @entangle('values').live,
            colors: @entangle('colors').live,
            init() {
              let chart = new Chart(this.$refs.canvas.getContext('2d'), {
                type: 'pie',
                data: {
                  labels: this.labels,
                  datasets: [
                    {
                      data: this.values,
                    },
                  ],
                },
                options: {
                  cutout: {{ $doughnut ? '"50%"': '0' }},
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: false,
                    }
                  },
                  scales: {
                    x: {
                      display: false,

                    },
                    y: {
                      display: false,
                    }
                  },
                  elements: {
                    arc: {
                        backgroundColor: this.colors,
                    }
                  }
                }
              })

              this.$watch('values', () => {
                chart.data.labels = this.labels
                chart.data.datasets[0].data = this.values
                chart.update()
              })
            }
        }"
    >
        <canvas class="h-full w-full" x-ref="canvas" wire:ignore></canvas>
    </div>
</div>
