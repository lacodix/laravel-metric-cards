<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Lacodix\LaravelMetricCards\Enums\TrendUnit;

/**
 * @method countByMinutes(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method countByHours(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method countByDays(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method countByWeeks(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method countByMonths(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method countByQuarters(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByMinutes(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByHours(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByDays(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByWeeks(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByMonths(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method sumByQuarters(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByMinutes(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByHours(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByDays(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByWeeks(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByMonths(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method minByQuarters(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByMinutes(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByHours(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByDays(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByWeeks(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByMonths(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method maxByQuarters(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByMinutes(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByHours(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByDays(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByWeeks(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByMonths(string $class, ?string $column = null, ?string $dateColumn = null)
 * @method avgByQuarters(string $class, ?string $column = null, ?string $dateColumn = null)
 */
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

    protected function run(
        string $function,
        TrendUnit $unit,
        string|Builder $model,
        ?string $column = null,
        ?string $dateColumn = null
    ): array {
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

    public function __call($method, $params): mixed
    {
        if (! Str::contains($method, 'By')) {
            return null;
        }

        [$function, $unit] = explode('By', $method);

        if (! in_array(strtolower($function), ['count', 'sum', 'min', 'max', 'avg'])
            || ! in_array(strtolower($unit), ['days', 'weeks', 'months', 'quarters', 'hours', 'minutes'])) {
            return null;
        }

        return $this->run(
            strtolower($function),
            TrendUnit::from(Str::singular(strtolower($unit))),
            ...$params
        );
    }

    protected function methodNotFound(string $method): never
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
