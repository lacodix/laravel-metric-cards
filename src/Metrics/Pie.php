<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Illuminate\View\View;

abstract class Pie extends Metric
{
    protected string $component = 'pie';
    public bool $doughnut = false;

    /** @var array<int,string>  */
    public array $colors = [
        '#FF6D60',
        '#009FBD',
        '#F7D060',
        '#98D8AA',
        '#77037B',
        '#210062',
        '#F9E2AF',
    ];

    public int $total;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    /** @var array<float> $percentages */
    public array $percentages;

    /** @return array<int|float> */
    abstract public function value(): array;

    public function total(): string
    {
        return '(Total ' . ($this->total ?? '') .')';
    }

    public function label(): string
    {
        return ':label (:number - :percentage%)';
    }

    public function render(): View
    {
        $this->calculate();

        return parent::render();
    }

    protected function calculate()
    {
        $values = $this->value();

        $this->values = array_values($values);
        $this->total = array_sum($this->values);
        $this->percentages = array_map(fn ($val) => round(100/$this->total*$val, 2), $this->values);

        $this->labels = collect(array_keys($values))
            ->map(fn ($label, $key) => __($this->label(), [
                'label' => $label,
                'number' => $this->values[$key],
                'percentage' => $this->percentages[$key],
            ]))
            ->all();
    }
}
