<div class="bg-white dark:bg-gray-800 mb-8 rounded-md shadow-sm p-4">
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

    <div class="overflow-hidden">
        <canvas
            data-te-chart="line"
            data-te-dataset-data="[{{ join(',', $this->values) }}]">
        </canvas>
    </div>
</div>
