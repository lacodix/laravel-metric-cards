<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Lacodix\LaravelMetricCards\Enums\TrendUnit;

abstract class Pie extends Metric
{
    protected string $component = 'pie';
    public bool $doughnut = false;

    public int $previousValue;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    public int $total;

    /** @return array<int|float> */
    abstract public function value(): array;

    public function mount(): void
    {
    }

    public function render(): View
    {
        $this->calculate();

        return parent::render();
    }

    protected function calculate()
    {
        $values = $this->value();

        $this->labels = array_keys($values);
        $this->values = array_values($values);
        $this->total = array_sum($this->values);
    }
}
