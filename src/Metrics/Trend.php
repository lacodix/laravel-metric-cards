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
 * @method array countByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array countByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array countByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array countByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array countByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array countByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array sumByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array minByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array maxByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 * @method array avgByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null)
 */
abstract class Trend extends Metric
{
    public int $previousValue;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    public int $period;
    protected string $component = 'trend';

    public function __call($method, $params): mixed
    {
        if (! Str::contains($method, 'By')) {
            return null;
        }

        [$function, $unit] = explode('By', (string) $method);

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
        $query = $model instanceof Builder ? $model : (new $model())->newQuery();
        $column ??= $query->getModel()->getQualifiedKeyName();
        $dateColumn ??= $query->getModel()->getCreatedAtColumn();
        $startingDate = $this->getStartingDate($unit);

        $groupBy = $this->getGroupBy($unit, $dateColumn);

        $results = $query
            ->select(DB::raw("{$groupBy} as period, {$function}({$column}) as aggregate"))
            ->whereBetween($dateColumn, [$startingDate, now()])
            ->groupBy(DB::raw($groupBy))
            ->orderBy('period')
            ->pluck('aggregate', 'period');

        $periods = $this->getAllPeriods($startingDate, now(), $unit);

        return array_merge(array_fill_keys($periods, 0), $results->all());
    }

    protected function calculate(): void
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

    protected function getGroupBy(TrendUnit $unit, string $column): string
    {
        $driver = DB::connection()->getDriverName();

        // MySQL: use DATE_FORMAT() and year()/month()/ceil()
        if ($driver === 'mysql') {
            return match ($unit) {
                TrendUnit::QUARTER => "concat(year({$column}),'-',ceil(month({$column})/3))",
                TrendUnit::MONTH => "date_format({$column}, '%Y-%m')",
                TrendUnit::WEEK => "date_format({$column}, '%x-%v')",
                TrendUnit::DAY => "date_format({$column}, '%Y-%m-%d')",
                TrendUnit::HOUR => "date_format({$column}, '%Y-%m-%d %H:00')",
                TrendUnit::MINUTE => "date_format({$column}, '%Y-%m-%d %H:%i:00')",
            };
        }

        // SQLite (and any others): use strftime()
        // Note: SQLite’s strftime always returns TEXT, so we wrap the quarter in a CAST back to integer.
        return match ($unit) {
            TrendUnit::QUARTER =>
                // year || '-' || quarter
                "strftime('%Y', {$column}) || '-' || " .
                "cast((cast(strftime('%m', {$column}) as integer) + 2) / 3 as integer)",
            TrendUnit::MONTH => "strftime('%Y-%m', {$column})",
            TrendUnit::WEEK => "strftime('%Y-%W', {$column})",
            TrendUnit::DAY => "strftime('%Y-%m-%d', {$column})",
            TrendUnit::HOUR => "strftime('%Y-%m-%d %H:00', {$column})",
            TrendUnit::MINUTE => "strftime('%Y-%m-%d %H:%M:00', {$column})",
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
            TrendUnit::QUARTER => $date->year . '-' . (int) ceil($date->month / 3),
            TrendUnit::MONTH => $date->format('Y-m'),
            TrendUnit::WEEK => $date->format('o-W'),
            TrendUnit::DAY => $date->format('Y-m-d'),
            TrendUnit::HOUR => $date->format('Y-m-d H:00'),
            TrendUnit::MINUTE => $date->format('Y-m-d H:i:00'),
        };
    }

    protected function methodNotFound(string $method): never
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            static::class,
            $method
        ));
    }
}
