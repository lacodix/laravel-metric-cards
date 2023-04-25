<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

abstract class Value extends Metric
{
    protected string $component = 'value';
    public ?int $previousValue = null;
    public int $currentValue;
    public int $period;
    public ?float $changePercentage = null;

    /** @return array<int|float> */
    abstract public function value(): array;

    public function options(): array
    {
        return [
            1 => '1 days',
            7 => '7 days',
            30 => '30 days',
            60 => '60 days',
            365 => '365 days',
        ];
    }

    public function link(): ?string
    {
        return null;
    }

    public function mount(): void
    {
        $this->period = current(array_keys($this->options()));
    }

    public function render(): View
    {
        $this->calculate();

        return parent::render();
    }

    protected function count(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', $model, $column, $dateColumn);
    }

    protected function max(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', $model, $column, $dateColumn);
    }

    protected function min(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', $model, $column, $dateColumn);
    }

    protected function avg(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', $model, $column, $dateColumn);
    }

    protected function sum(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', $model, $column, $dateColumn);
    }

    protected function run(string $function, string|Builder $model, ?string $column, ?string $dateColumn): array
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();
        $column ??= $query->getModel()->getQualifiedKeyName();

        return [
            round($query->clone()->whereBetween(
                $dateColumn ?? $query->getModel()->getCreatedAtColumn(),
                $this->previousPeriod(),
            )->{$function}($column)),
            round($query->clone()->whereBetween(
                $dateColumn ?? $query->getModel()->getCreatedAtColumn(),
                $this->currentPeriod(),
            )->{$function}($column)),
        ];
    }

    protected function previousPeriod(): array
    {
        return [
            now()->subDays($this->period * 2),
            now()->subDays($this->period),
        ];
    }

    protected function currentPeriod(): array
    {
        return [
            now()->subDays($this->period),
            now(),
        ];
    }

    protected function calculate()
    {
        $values = $this->value();
        $this->currentValue = $values[1] ?? $values[0];
        $this->previousValue = isset($values[1]) ? $values[0] : null;

        if (! is_null($this->previousValue)) {
            $diff = $this->currentValue - $this->previousValue;
            $divisor = $this->previousValue;
            $this->changePercentage = match (true) {
                $diff === 0 => 0,
                $divisor === 0 => null,
                default => round(100 / $divisor * $diff, 2),
            };
        }
    }
}
