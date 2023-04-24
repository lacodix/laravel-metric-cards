<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Illuminate\View\View;

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
