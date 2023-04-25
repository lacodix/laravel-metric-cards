@php
    $tag = $this->link() ? 'a' : 'div';
    $link = $this->link();
@endphp
<{{ $tag }}
    class="block bg-white dark:bg-gray-800 rounded-md shadow-sm p-4 flex flex-col justify-between
@if ($link) hover:no-underline hover:bg-gray-100 @endif"
data-metric-name="{{ $this->name() }}"
@if ($link)
    href="{{ $link }}"
@endif
>
<div class="flex justify-between mb-4">
    <div class="font-bold">{{ $this->title() }}</div>
    @unless(empty($this->options()))
        <div>
            <select wire:model="period" class="rounded-none py-1 px-2 text-sm">
                @foreach($this->options() as $key => $option)
                    <option value="{{ $key }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    @endunless
</div>

<div class="flex items-end justify-between">
    @unless(is_null($previousValue))
        <div class="flex">
            @if (! is_null($changePercentage) && $changePercentage < 0)
                <svg class="h-5 mr-2 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>trending-down</title><path d="M16,18L18.29,15.71L13.41,10.83L9.41,14.83L2,7.41L3.41,6L9.41,12L13.41,8L19.71,14.29L22,12V18H16Z" /></svg>
            @else
                <svg class="h-5 mr-2 @if (is_null($changePercentage) || $changePercentage > 0) fill-green-500 @endif" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>trending-up</title><path d="M16,6L18.29,8.29L13.41,13.17L9.41,9.17L2,16.59L3.41,18L9.41,12L13.41,16L19.71,9.71L22,12V6H16Z" /></svg>
            @endif
            @if (is_null($changePercentage))
                <svg class="h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>tilde</title><path d="M2,15C2,15 2,9 8,9C12,9 12.5,12.5 15.5,12.5C19.5,12.5 19.5,9 19.5,9H22C22,9 22,15 16,15C12,15 10.5,11.5 8.5,11.5C4.5,11.5 4.5,15 4.5,15H2" /></svg>%
            @else
                {{ $changePercentage }}%
            @endif
        </div>
    @endunless
    <div class="flex items-baseline">
        @unless(is_null($previousValue))
            <div>{{ $previousValue }}</div>
            <svg class="h-5 self-end mb-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>arrow-right-thin</title><path d="M14 16.94V12.94H5.08L5.05 10.93H14V6.94L19 11.94Z" /></svg>
        @endunless
        <div class="text-6xl">{{ $currentValue }}</div>
    </div>
</div>
</{{ $tag }}>
