<div class="bg-white dark:bg-gray-800 mb-8 rounded-md shadow-sm p-4 relative">
    <div class="flex justify-between mb-4">
        <div class="font-bold">{{ $this->title() }}</div>
        <div>{{ $total }}</div>
    </div>

    <ul>
        @foreach($values as $value)
            <li>
                {{ $value }}
            </li>
        @endforeach
    </ul>

    <div
        class="overflow-hidden absolute bottom-0 right-0 h-2/3 w-1/3"
        x-data="{
            labels: @entangle('labels'),
            values: @entangle('values'),
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
        <canvas class="h-2/3" x-ref="canvas" wire:ignore></canvas>
    </div>
</ul>