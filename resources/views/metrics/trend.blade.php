<div
    class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-4 relative"
    data-metric-name="{{ $this->name() }}"
>
    <div class="flex justify-between mb-4">
        <div class="font-bold">{{ $this->title() }}</div>
        <div>
            <select wire:model="period" class="rounded-none py-1 px-2 text-sm">
                @foreach($this->options() as $key => $option)
                    <option value="{{ $key }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div
        class="overflow-hidden absolute w-full bottom-0 left-0 h-1/2"
        x-data="{
            labels: @entangle('labels'),
            values: @entangle('values'),
            init() {
              let chart = new Chart(this.$refs.canvas.getContext('2d'), {
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
        <canvas class="h-full w-full" x-ref="canvas" wire:ignore></canvas>
    </div>
</div>
