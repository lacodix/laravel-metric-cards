<?php

declare(strict_types=1);

namespace Lacodix\LaravelMetricCards\Support;

use Lacodix\LaravelMetricCards\Enums\ColorDerivation;

/**
 * Resolves exactly one color per chart segment.
 *
 * A chart may have more segments than the palette has colors, because the
 * number of segments usually comes from data. Handing out a short color list
 * is what used to break the pie card: the legend read past the end of the
 * array and Chart.js silently painted the surplus segments in its own default
 * color. Callers therefore ask for a list of a given length and always get
 * exactly that length back.
 */
final class SegmentColors
{
    /**
     * The golden angle. Rotating the hue by it per palette run spreads derived
     * colors as evenly as possible around the color wheel, so consecutive runs
     * do not land on a neighbouring hue.
     */
    private const GOLDEN_ANGLE = 137.508;

    private const LIGHTNESS_STEP = 0.18;

    /**
     * Derived brightness stays away from plain black and white, where every
     * color would end up looking the same.
     */
    private const MIN_LIGHTNESS = 0.15;

    private const MAX_LIGHTNESS = 0.85;

    /**
     * One color per segment, in segment order. The first colors are the
     * palette exactly as configured; only once it is exhausted does the
     * derivation kick in, so a chart that fits into the palette looks the same
     * as it always did.
     *
     * An empty palette (or a palette of unreadable values) yields an empty
     * list: callers fall back to the package palette or to Chart.js defaults
     * instead of getting made-up colors.
     *
     * The palette is typed loosely on purpose: it usually comes straight from
     * the application config, where anything may end up.
     *
     * @param  array<int, mixed>  $palette
     * @return array<int, string>
     */
    public static function resolve(array $palette, int $count, ColorDerivation $derivation): array
    {
        $palette = self::usable($palette);

        if ($palette === [] || $count < 1) {
            return [];
        }

        $size = count($palette);
        $colors = [];

        for ($index = 0; $index < $count; $index++) {
            $run = intdiv($index, $size);
            $base = $palette[$index % $size];

            $colors[] = $run === 0 ? $base : self::derive($base, $run, $derivation);
        }

        return $colors;
    }

    /**
     * The palette entries this resolver can actually work with. Callers ask
     * before they decide whether a palette is worth using at all, so that
     * "empty palette" means the same thing on both sides: a caller that only
     * checked for an empty array would consider `[123]` a configured palette,
     * hand it over, and get nothing back.
     *
     * Values are handed through unchanged - trimming only decides whether an
     * entry counts, it does not rewrite what a palette carries.
     *
     * @param  array<int, mixed>  $palette
     * @return array<int, string>
     */
    public static function usable(array $palette): array
    {
        $usable = [];

        foreach ($palette as $color) {
            if (is_string($color) && trim($color) !== '') {
                $usable[] = $color;
            }
        }

        return $usable;
    }

    /**
     * A color that is not a readable hex value is handed back untouched - the
     * palette may legitimately carry `rgb()`, `hsl()` or a CSS variable, and
     * guessing at it would be worse than repeating it. Such a palette does not
     * get distinct derived colors; that is the documented limit of the
     * derivation, not something to paper over with an invented color.
     */
    private static function derive(string $color, int $run, ColorDerivation $derivation): string
    {
        if ($derivation === ColorDerivation::REPEAT) {
            return $color;
        }

        $hsl = self::toHsl($color);

        if ($hsl === null) {
            return $color;
        }

        [$hue, $saturation, $lightness, $alpha] = $hsl;

        // Rotating the hue of a grey, black or white does nothing - they have no
        // hue to rotate. Varying the brightness is the only way to tell such
        // segments apart, so HUE borrows it rather than repeating the color.
        if ($derivation === ColorDerivation::HUE && $saturation >= 1e-9) {
            return self::toHex(fmod($hue + $run * self::GOLDEN_ANGLE, 360.0), $saturation, $lightness, $alpha);
        }

        return self::toHex($hue, $saturation, self::shiftLightness($lightness, $run), $alpha);
    }

    /**
     * Alternates around the palette color instead of walking in one direction,
     * so the derived colors stay near the original: run 1 is lighter, run 2 is
     * darker by the same amount, run 3 lighter by twice as much, and so on.
     */
    private static function shiftLightness(float $lightness, int $run): float
    {
        $distance = intdiv($run + 1, 2) * self::LIGHTNESS_STEP;
        $shifted = $lightness + ($run % 2 === 1 ? $distance : -$distance);

        return min(self::MAX_LIGHTNESS, max(self::MIN_LIGHTNESS, $shifted));
    }

    /**
     * Reads the four hex notations CSS allows (#rgb, #rgba, #rrggbb, #rrggbbaa).
     * Any transparency is carried through untouched, so a translucent palette
     * color does not silently turn opaque when it is derived.
     *
     * @return array{0: float, 1: float, 2: float, 3: string}|null Hue in degrees, saturation and lightness as 0..1, alpha as hex digits
     */
    private static function toHsl(string $color): ?array
    {
        $hex = ltrim(trim($color), '#');
        $alpha = '';

        if (preg_match('/^[0-9a-fA-F]{3,4}$/', $hex) === 1) {
            $expanded = '';

            foreach (str_split($hex) as $digit) {
                $expanded .= $digit . $digit;
            }

            $hex = $expanded;
        }

        if (preg_match('/^[0-9a-fA-F]{8}$/', $hex) === 1) {
            $alpha = substr($hex, 6, 2);
            $hex = substr($hex, 0, 6);
        }

        if (preg_match('/^[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return null;
        }

        $red = hexdec(substr($hex, 0, 2)) / 255;
        $green = hexdec(substr($hex, 2, 2)) / 255;
        $blue = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $lightness = ($max + $min) / 2;
        $delta = $max - $min;

        if ($delta < 1e-9) {
            return [0.0, 0.0, $lightness, $alpha];
        }

        $saturation = $lightness > 0.5
            ? $delta / (2 - $max - $min)
            : $delta / ($max + $min);

        $hue = match (true) {
            $max === $red => ($green - $blue) / $delta + ($green < $blue ? 6 : 0),
            $max === $green => ($blue - $red) / $delta + 2,
            default => ($red - $green) / $delta + 4,
        };

        return [$hue * 60, $saturation, $lightness, $alpha];
    }

    private static function toHex(float $hue, float $saturation, float $lightness, string $alpha = ''): string
    {
        $hue = fmod(fmod($hue, 360.0) + 360.0, 360.0) / 360;

        if ($saturation < 1e-9) {
            $channel = (int) round($lightness * 255);

            return sprintf('#%02x%02x%02x', $channel, $channel, $channel) . $alpha;
        }

        $upper = $lightness < 0.5
            ? $lightness * (1 + $saturation)
            : $lightness + $saturation - $lightness * $saturation;
        $lower = 2 * $lightness - $upper;

        return sprintf(
            '#%02x%02x%02x',
            (int) round(self::channel($lower, $upper, $hue + 1 / 3) * 255),
            (int) round(self::channel($lower, $upper, $hue) * 255),
            (int) round(self::channel($lower, $upper, $hue - 1 / 3) * 255),
        ) . $alpha;
    }

    private static function channel(float $lower, float $upper, float $hue): float
    {
        $hue = fmod(fmod($hue, 1.0) + 1.0, 1.0);

        return match (true) {
            $hue < 1 / 6 => $lower + ($upper - $lower) * 6 * $hue,
            $hue < 1 / 2 => $upper,
            $hue < 2 / 3 => $lower + ($upper - $lower) * (2 / 3 - $hue) * 6,
            default => $lower,
        };
    }
}
