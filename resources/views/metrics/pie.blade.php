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

    @include('lacodix-metrics::metrics._assets')

    <div
        class="flex flex-col-reverse gap-4 @xs:flex-row justify-between items-center"
        x-data="metricPieChart({
            labels: @entangle('labels').live,
            values: @entangle('values').live,
            colors: @entangle('colors').live,
            invisible: @entangle('invisibleValues').live,
            doughnut: {{ $doughnut ? 'true' : 'false' }},
        })"
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
