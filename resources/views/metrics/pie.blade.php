<div
    class="bg-white dark:bg-gray-800 relative @container
    @if (!$this->flat)
    rounded-md shadow-sm p-4
    @endif
    "
    data-metric-name="{{ $this->name() }}"
>
    <div class="flex flex-col @md:flex-row justify-between mb-4">
        <div class="font-bold">{!! $this->title() !!}</div>
        <div class="text-xs text-gray-600">{!! $this->total() !!}</div>
    </div>

    <div
        class="flex flex-col-reverse gap-4 @xs:flex-row justify-between items-center"
        x-data="{
            labels: @entangle('labels').live,
            values: @entangle('values').live,
            colors: @entangle('colors').live,
            invisible: @entangle('invisibleValues').live,
            init() {
                let chart = new Chart(this.$refs.canvas.getContext('2d'), {
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
                      cutout: {{ $doughnut ? '"50%"': '0' }},
                      maintainAspectRatio: false,
                      plugins: {
                        legend: {
                          display: false,
                        },
                        tooltip: {
                          enabled: false,
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
                    chart.data.labels = this.labels.slice()
                    chart.data.datasets[0].data = this.values.slice()

                    chart.update()
                  })

                  checkInvisible = function(invisible) {
                    // for a single‐dataset pie, datasetIndex is always 0
                    const dsMeta = chart.getDatasetMeta(0);

                    dsMeta.data.forEach((arc, idx) => {
                        const shouldBeHidden  = invisible.includes(idx);
                        const isCurrentlyVisible = chart.getDataVisibility(idx);

                        // if its desired hidden‐state differs from current, toggle it
                        if (shouldBeHidden && isCurrentlyVisible) {
                            chart.toggleDataVisibility(idx);
                        }
                        else if (!shouldBeHidden && !isCurrentlyVisible) {
                            chart.toggleDataVisibility(idx);
                        }
                    })

                    chart.update()
                  }

                  this.$watch('invisible', (values) => {
                    checkInvisible(values);
                  })

                  checkInvisible(this.invisible);
               },

               toggle(key) {
                    const arr = this.invisible

                    const idx = arr.indexOf(key)
                    if (idx !== -1) {
                        // key is already “invisible” → remove it
                        arr.splice(idx, 1)
                    } else {
                        // key wasn’t invisible → add it
                        arr.push(key)
                    }
               },

               isInvisible(key) {
                    const arr = this.invisible

                    return arr.indexOf(key) !== -1
               },
        }"
    >
        <ul>
            @foreach($values as $key => $value)
                <li
                    class="text-xs text-gray-600 mb-1 hover:cursor-pointer"
                    :class="{'line-through': isInvisible({{ $key }})}"
                    @click="toggle({{ $key }})"
                >
                    <span class="inline-block rounded-full w-2 h-2 mr-2" style="background-color: {{ $colors[$key] }}"></span>
                    {!! $labels[$key] ?? '' !!}
                </li>
            @endforeach
        </ul>

        <div class="overflow-hidden @xs:w-1/3">
            <canvas class="h-full w-full" x-ref="canvas" wire:ignore></canvas>
        </div>
    </div>
</div>
