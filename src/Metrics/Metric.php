<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

abstract class Metric extends Component
{
    protected string $component;
    protected string $title;

    public function title(): string
    {
        $this->title ??= ucwords(str_replace('_', ' ', Str::snake(class_basename($this))));

        return $this->title;
    }

    public function render(): View
    {
        return view(config('metric-cards.metrics_view_prefix') . $this->component);
    }
}
