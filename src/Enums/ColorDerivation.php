<?php

declare(strict_types=1);

namespace Lacodix\LaravelMetricCards\Enums;

/**
 * How a chart gets colors for segments the palette does not cover.
 *
 * A pie metric may produce more segments than the palette has colors - the
 * number of segments often comes from data, not from the code. Every strategy
 * keeps the palette itself untouched: the first colors of a chart are always
 * the palette in its configured order, derivation only starts once it is
 * exhausted.
 */
enum ColorDerivation: string
{
    /**
     * Rotate the hue by the golden angle per palette run. Maximum distinction
     * between segments, at the price of leaving the configured color family.
     */
    case HUE = 'hue';

    /**
     * Keep the hue and vary the brightness per palette run. Stays inside the
     * configured color family, but distinguishes less clearly - and because
     * brightness is bounded, a heavily recycled palette eventually repeats.
     */
    case LIGHTNESS = 'lightness';

    /**
     * Cycle the palette unchanged. Segments repeat colors - only useful when
     * the palette is known to be long enough anyway.
     */
    case REPEAT = 'repeat';
}
