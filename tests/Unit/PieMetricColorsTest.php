<?php

declare(strict_types=1);

use Lacodix\LaravelMetricCards\Enums\ColorDerivation;
use Lacodix\LaravelMetricCards\Support\SegmentColors;
use Livewire\Livewire;
use Tests\Metrics\PostsPerGroup;

beforeEach(function () {
    config()->set('metric-cards.chart.dataset_colors', ['#6c5cff', '#22c55e', '#f59e0b']);
    config()->set('metric-cards.chart.color_derivation', 'hue');
});

function segmentColors(int $groups): array
{
    return Livewire::test(PostsPerGroup::class)
        ->set('groups', $groups)
        ->get('segmentColors');
}

test('a metric gets exactly one color per segment', function () {
    expect(segmentColors(2))->toHaveCount(2)
        ->and(segmentColors(3))->toHaveCount(3)
        ->and(segmentColors(11))->toHaveCount(11);
});

test('a chart that fits into the palette gets the palette unchanged', function () {
    expect(segmentColors(3))->toBe(['#6c5cff', '#22c55e', '#f59e0b']);
});

test('the palette comes first even when the chart runs past it', function () {
    expect(array_slice(segmentColors(7), 0, 3))->toBe(['#6c5cff', '#22c55e', '#f59e0b']);
});

test('derived colors are distinct from the palette and from each other', function () {
    $colors = segmentColors(9);

    expect($colors)->toHaveCount(9)
        ->and(array_unique($colors))->toHaveCount(9);
});

test('the lightness strategy keeps the hue of the palette color', function () {
    config()->set('metric-cards.chart.color_derivation', 'lightness');

    $colors = segmentColors(6);

    // Segment 3 is the second run of palette color 0: same hue, other brightness.
    expect(hue($colors[3]))->toEqualWithDelta(hue('#6c5cff'), 1.0)
        ->and($colors[3])->not->toBe('#6c5cff');
});

test('the hue strategy leaves the hue of the palette color', function () {
    config()->set('metric-cards.chart.color_derivation', 'hue');

    $colors = segmentColors(6);

    expect(abs(hue($colors[3]) - hue('#6c5cff')))->toBeGreaterThan(20.0);
});

test('the repeat strategy cycles the palette', function () {
    config()->set('metric-cards.chart.color_derivation', 'repeat');

    expect(segmentColors(5))->toBe(['#6c5cff', '#22c55e', '#f59e0b', '#6c5cff', '#22c55e']);
});

test('an unknown derivation in the config falls back to the default', function () {
    config()->set('metric-cards.chart.color_derivation', 'nonsense');

    expect(segmentColors(5))->toBe(segmentColors(5))
        ->and(array_unique(segmentColors(5)))->toHaveCount(5);
});

test('a metric palette overrides the configured one', function () {
    Livewire::test(PostsPerGroup::class)
        ->set('groups', 2)
        ->set('colors', ['#ff0000', '#00ff00'])
        ->assertSet('segmentColors', ['#ff0000', '#00ff00']);
});

test('an empty configured palette falls back to the built-in one', function () {
    config()->set('metric-cards.chart.dataset_colors', []);

    // Legend and canvas both need real colors - handing out an empty list left
    // the legend colorless while Chart.js painted the canvas on its own.
    expect(segmentColors(3))->toBe(['#009FBD', '#F7D060', '#FF6D60']);
});

test('a configured palette without usable entries falls back too', function () {
    // Not just an empty array: anything the resolver cannot use has to fall
    // through, otherwise the palette counts as configured and yields nothing.
    config()->set('metric-cards.chart.dataset_colors', [123, null, '', '   ', ['#fff']]);

    expect(segmentColors(3))->toBe(['#009FBD', '#F7D060', '#FF6D60']);
});

test('unusable entries are dropped from an otherwise fine palette', function () {
    config()->set('metric-cards.chart.dataset_colors', ['#6c5cff', null, '#22c55e']);

    expect(segmentColors(2))->toBe(['#6c5cff', '#22c55e']);
});

test('a metric palette without usable entries falls back to the configured one', function () {
    Livewire::test(PostsPerGroup::class)
        ->set('groups', 2)
        ->set('colors', ['', '  '])
        ->assertSet('segmentColors', ['#6c5cff', '#22c55e']);
});

test('greys are derived by brightness because they have no hue to rotate', function () {
    config()->set('metric-cards.chart.dataset_colors', ['#808080']);
    config()->set('metric-cards.chart.color_derivation', 'hue');

    $colors = segmentColors(3);

    expect($colors[0])->toBe('#808080')
        ->and(array_unique($colors))->toHaveCount(3);
});

test('transparency of a palette color survives the derivation', function () {
    $colors = SegmentColors::resolve(['#6c5cffcc'], 2, ColorDerivation::HUE);

    expect($colors[0])->toBe('#6c5cffcc')
        ->and($colors[1])->toEndWith('cc')
        ->and($colors[1])->not->toBe('#6c5cffcc');
});

test('the legend renders every segment even beyond the palette', function () {
    Livewire::test(PostsPerGroup::class)
        ->set('groups', 11)
        ->assertOk()
        ->assertSee('Group 11');
});

test('a color the resolver cannot read is handed back untouched', function () {
    // Documented limit: only hex colors can be derived. A palette of rgb()/hsl()
    // values or CSS variables repeats instead of inventing something.
    $colors = SegmentColors::resolve(['var(--brand)', 'rgb(1, 2, 3)'], 4, ColorDerivation::HUE);

    expect($colors)->toBe(['var(--brand)', 'rgb(1, 2, 3)', 'var(--brand)', 'rgb(1, 2, 3)']);
});

test('shorthand hex colors are derived just like full ones', function () {
    $colors = SegmentColors::resolve(['#f00'], 2, ColorDerivation::HUE);

    expect($colors[0])->toBe('#f00')
        ->and($colors[1])->toMatch('/^#[0-9a-f]{6}$/')
        ->and($colors[1])->not->toBe('#f00');
});

test('no segments means no colors', function () {
    expect(SegmentColors::resolve(['#6c5cff'], 0, ColorDerivation::HUE))->toBe([]);
});

/**
 * The hue of a hex color in degrees - enough to tell "same color family" from
 * "rotated away" without pulling in a color library.
 */
function hue(string $hex): float
{
    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $red = hexdec(substr($hex, 0, 2)) / 255;
    $green = hexdec(substr($hex, 2, 2)) / 255;
    $blue = hexdec(substr($hex, 4, 2)) / 255;

    $max = max($red, $green, $blue);
    $delta = $max - min($red, $green, $blue);

    if ($delta < 1e-9) {
        return 0.0;
    }

    $hue = match (true) {
        $max === $red => ($green - $blue) / $delta + ($green < $blue ? 6 : 0),
        $max === $green => ($blue - $red) / $delta + 2,
        default => ($red - $green) / $delta + 4,
    };

    return $hue * 60;
}
