<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Lacodix\LaravelMetricCards\Enums\TrendUnit;

abstract class Trend extends Metric
{
    protected string $component = 'trend';
    public int $previousValue;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    public int $period;

    /** @return array<int|float> */
    abstract public function value(): array;

    public function options(): array
    {
        return [
            5 => '5 days',
            10 => '10 days',
            15 => '15 days',
            30 => '30 days',
        ];
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

    protected function countByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function run(string $function, TrendUnit $unit, string|Builder $model, ?string $column, ?string $dateColumn): array
    {
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();
        $column ??= $query->getModel()->getQualifiedKeyName();
        $dateColumn ??= $query->getModel()->getCreatedAtColumn();
        $startingDate = $this->getStartingDate($unit);

        $groupBy = $this->getGroupBy($query, $unit, $dateColumn);

        $results = $query
            ->select(DB::raw("{$groupBy} as period, {$function}({$column}) as aggregate"))
            ->whereBetween($dateColumn, [$startingDate, now()])
            ->groupBy(DB::raw($groupBy))
            ->orderBy('period')
            ->pluck('aggregate', 'period');

        $periods = $this->getAllPeriods($startingDate, now(), $unit);

        return array_merge(array_fill_keys($periods, 0), $results->all());
    }

    protected function calculate()
    {
        $values = $this->value();

        $this->labels = array_keys($values);
        $this->values = array_values($values);
    }

    protected function getStartingDate(TrendUnit $unit): Carbon
    {
        return match ($unit) {
            TrendUnit::QUARTER => now()->subQuarters($this->period - 1)->firstOfQuarter()->startOfDay(),
            TrendUnit::MONTH => now()->subMonths($this->period - 1)->firstOfMonth()->startOfDay(),
            TrendUnit::WEEK => now()->subWeeks($this->period - 1)->startOfWeek()->startOfDay(),
            TrendUnit::DAY => now()->subDays($this->period - 1)->startOfDay(),
            TrendUnit::HOUR => now()->subHours($this->period - 1)->minute(0)->second(0),
            TrendUnit::MINUTE => now()->subMinutes($this->period - 1)->second(0),
        };
    }

    protected function getGroupBy(Builder $query, TrendUnit $unit, $column): string
    {
        return match ($unit) {
            TrendUnit::QUARTER => "concat(year({$column}),'-',ceil(month({$column})/3))",
            TrendUnit::MONTH => "date_format({$column}, '%Y-%m')",
            TrendUnit::WEEK => "date_format({$column}, '%x-%v')",
            TrendUnit::DAY => "date_format({$column}, '%Y-%m-%d')",
            TrendUnit::HOUR => "date_format({$column}, '%Y-%m-%d %H:00')",
            TrendUnit::MINUTE => "date_format({$column}, '%Y-%m-%d %H:%i:00')",
        };
    }

    protected function getAllPeriods(Carbon $startDate, Carbon $endDate, TrendUnit $unit): array
    {
        $periods = [];
        $startDate = $startDate->clone();

        do {
            $periods[] = $this->formatForPeriod($startDate, $unit);
            $this->addInterval($startDate, $unit);
        } while ($startDate->lt($endDate));

        return $periods;
    }

    protected function addInterval(Carbon $date, TrendUnit $unit): Carbon
    {
        return match ($unit) {
            TrendUnit::QUARTER => $date->addQuarter(),
            TrendUnit::MONTH => $date->addMonth(),
            TrendUnit::WEEK => $date->addWeek(),
            TrendUnit::DAY => $date->addDay(),
            TrendUnit::HOUR => $date->addHour(),
            TrendUnit::MINUTE => $date->addMinute(),
        };
    }

    protected function formatForPeriod(Carbon $date, TrendUnit $unit): string
    {
        return match ($unit) {
            TrendUnit::QUARTER => $date->year . '-' . (int) ceil($date->month/3),
            TrendUnit::MONTH => $date->format('Y-m'),
            TrendUnit::WEEK => $date->format('o-W'),
            TrendUnit::DAY => $date->format('Y-m-d'),
            TrendUnit::HOUR => $date->format('Y-m-d H:00'),
            TrendUnit::MINUTE => $date->format('Y-m-d H:i:00'),
        };
    }
}
