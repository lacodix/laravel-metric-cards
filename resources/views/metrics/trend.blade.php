<div
    class="bg-white dark:bg-gray-800 relative @container
    @if (!$this->flat)
    rounded-md shadow-sm p-4
    @endif
    "
    data-metric-name="{{ $this->name() }}"
>
    <div class="flex flex-col @md:flex-row justify-between mb-4">
        <div class="font-bold">{{ $this->title() }}</div>
        <div>
            <select wire:model.live="period" class="rounded-none py-1 px-2 text-sm">
                @foreach($this->options() as $key => $option)
                    <option value="{{ $key }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @include('lacodix-metrics::metrics._assets')

    <div
        class="overflow-hidden absolute w-full bottom-0 left-0 h-1/2"
        x-data="metricTrendChart({
            labels: @entangle('labels').live,
            values: @entangle('values').live,
        })"
    >
        <canvas class="h-full w-full" x-ref="canvas" wire:ignore></canvas>
    </div>
</div>
