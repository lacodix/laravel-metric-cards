<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Lacodix\LaravelMetricCards\Enums\TrendUnit;

trait NormalizesTrendPeriod
{
    public function hydrateNormalizesTrendPeriod(): void
    {
        $this->normalizePeriod();
    }

    public function updatedNormalizesTrendPeriod($path, $value): void
    {
        if ($path === 'period') {
            $this->normalizePeriod();
        }
    }
}

abstract class Trend extends Metric
{
    use NormalizesTrendPeriod;

    public int $previousValue;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    public int $period;
    protected string $component = 'trend';

    protected function countByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::MINUTE, $model, $column, $dateColumn);
    }

    protected function countByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::HOUR, $model, $column, $dateColumn);
    }

    protected function countByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function countByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::WEEK, $model, $column, $dateColumn);
    }

    protected function countByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::MONTH, $model, $column, $dateColumn);
    }

    protected function countByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('count', TrendUnit::QUARTER, $model, $column, $dateColumn);
    }

    protected function sumByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::MINUTE, $model, $column, $dateColumn);
    }

    protected function sumByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::HOUR, $model, $column, $dateColumn);
    }

    protected function sumByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function sumByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::WEEK, $model, $column, $dateColumn);
    }

    protected function sumByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::MONTH, $model, $column, $dateColumn);
    }

    protected function sumByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('sum', TrendUnit::QUARTER, $model, $column, $dateColumn);
    }

    protected function minByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::MINUTE, $model, $column, $dateColumn);
    }

    protected function minByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::HOUR, $model, $column, $dateColumn);
    }

    protected function minByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function minByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::WEEK, $model, $column, $dateColumn);
    }

    protected function minByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::MONTH, $model, $column, $dateColumn);
    }

    protected function minByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('min', TrendUnit::QUARTER, $model, $column, $dateColumn);
    }

    protected function maxByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::MINUTE, $model, $column, $dateColumn);
    }

    protected function maxByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::HOUR, $model, $column, $dateColumn);
    }

    protected function maxByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function maxByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::WEEK, $model, $column, $dateColumn);
    }

    protected function maxByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::MONTH, $model, $column, $dateColumn);
    }

    protected function maxByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('max', TrendUnit::QUARTER, $model, $column, $dateColumn);
    }

    protected function avgByMinutes(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::MINUTE, $model, $column, $dateColumn);
    }

    protected function avgByHours(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::HOUR, $model, $column, $dateColumn);
    }

    protected function avgByDays(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::DAY, $model, $column, $dateColumn);
    }

    protected function avgByWeeks(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::WEEK, $model, $column, $dateColumn);
    }

    protected function avgByMonths(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::MONTH, $model, $column, $dateColumn);
    }

    protected function avgByQuarters(string|Builder $model, ?string $column = null, ?string $dateColumn = null): array
    {
        return $this->run('avg', TrendUnit::QUARTER, $model, $column, $dateColumn);
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

    private function normalizePeriod(): void
    {
        $options = $this->options();

        if (! isset($this->period) || ! array_key_exists($this->period, $options)) {
            $this->period = current(array_keys($options));
        }
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
        $this->normalizePeriod();

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
            // yyyy-q
            TrendUnit::QUARTER =>
                "strftime('%Y', {$column})"
                . " || '-' || "
                . "cast((cast(strftime('%m', {$column}) as integer) + 2) / 3 as integer)",

            // yyyy-mm
            TrendUnit::MONTH   => "strftime('%Y-%m', {$column})",

            // ISO week-year and week number, Monday-first
            //  SQLite's %W is Monday-first but zero-based, so we +1, and %Y is safe
            TrendUnit::WEEK    =>
                "strftime('%Y', {$column})"
                . " || '-' || "
                . "cast(cast(strftime('%W', {$column}) as integer) + 1 as text)",

            // yyyy-mm-dd
            TrendUnit::DAY     => "strftime('%Y-%m-%d', {$column})",

            // hour bucket: yyyy-mm-dd hh:00
            TrendUnit::HOUR    => "strftime('%Y-%m-%d %H:00', {$column})",

            // minute bucket: yyyy-mm-dd hh:ii:00
            TrendUnit::MINUTE  => "strftime('%Y-%m-%d %H:%M:00', {$column})",
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
