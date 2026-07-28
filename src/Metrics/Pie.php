<?php

namespace Lacodix\LaravelMetricCards\Metrics;

use Illuminate\View\View;
use Lacodix\LaravelMetricCards\Enums\ColorDerivation;
use Lacodix\LaravelMetricCards\Support\SegmentColors;

abstract class Pie extends Metric
{
    /**
     * The palette every pie card falls back to when neither the metric nor the
     * application configures one. These are the colors this package has always
     * painted pie charts with, so an application that never touched a color
     * setting keeps the look it had - and, more importantly, the resolution
     * below always has something to work with. Without it an empty palette
     * would leave the legend colorless while Chart.js painted the canvas on
     * its own: two views of the same data disagreeing about its colors.
     */
    protected const DEFAULT_PALETTE = [
        '#009FBD',
        '#F7D060',
        '#FF6D60',
        '#98D8AA',
        '#77037B',
        '#210062',
        '#F9E2AF',
    ];

    public bool $doughnut = false;

    public array $invisibleValues = [];

    /**
     * The palette this metric draws from, overriding the package palette
     * (`metric-cards.chart.dataset_colors`) for this card only. Leave it empty
     * to follow the configured palette - that is the one place a host
     * application changes its chart colors.
     *
     * @var array<int,string>
     */
    public array $colors = [];

    /**
     * One color per value, in value order, filled by calculate(). This is what
     * the legend and Chart.js render - never the palette above, which may be
     * shorter than the number of values.
     *
     * @var array<int,string>
     */
    public array $segmentColors = [];

    /**
     * Overrides `metric-cards.chart.color_derivation` for this metric, for the
     * cards where staying inside the color family beats telling segments apart
     * (or the other way round).
     */
    protected ?ColorDerivation $colorDerivation = null;

    public int $total;
    /** @var array<int> $values */
    public array $values;
    /** @var array<string> $labels */
    public array $labels;
    /** @var array<float> $percentages */
    public array $percentages;
    protected string $component = 'pie';

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

    protected function calculate(): void
    {
        $values = $this->value();

        $this->values = array_values($values);
        $this->total = array_sum($this->values);
        $this->percentages = array_map(
            fn ($val) => $this->total === 0 ? 0 : round(100 / $this->total * $val, 2),
            $this->values
        );

        $this->labels = collect(array_keys($values))
            ->map(fn ($label, $key) => __($this->label(), [
                'label' => $label,
                'number' => $this->values[$key],
                'percentage' => $this->percentages[$key],
            ]))
            ->all();

        $this->segmentColors = SegmentColors::resolve(
            $this->palette(),
            count($this->values),
            $this->colorDerivation ?? $this->derivationFromConfig(),
        );
    }

    /**
     * The metric's own palette, else the configured one, else the built-in one.
     * Every step asks the resolver what it can use rather than just checking
     * for an empty array: a palette of unusable entries has to fall through to
     * the next step, otherwise it would end up producing no colors at all.
     *
     * @return array<int,string>
     */
    protected function palette(): array
    {
        $configured = config('metric-cards.chart.dataset_colors', []);

        return SegmentColors::usable($this->colors)
            ?: (is_array($configured) ? SegmentColors::usable($configured) : [])
            ?: self::DEFAULT_PALETTE;
    }

    /**
     * An unknown value in the config falls back to the package default instead
     * of throwing: a typo in a color setting must not take the dashboard down.
     */
    protected function derivationFromConfig(): ColorDerivation
    {
        $configured = config('metric-cards.chart.color_derivation');

        return (is_string($configured) ? ColorDerivation::tryFrom($configured) : null)
            ?? ColorDerivation::HUE;
    }
}
