<?php

use Livewire\Livewire;
use Tests\Metrics\PostsPerHour;
use Tests\Metrics\PostsPerMinute;
use Tests\Metrics\PostsPerMonth;
use Tests\Metrics\PostsPerQuarter;
use Tests\Metrics\PostsPerWeek;
use Tests\Metrics\PostsPerDay;
use Tests\Models\Post;

test('trend metric calculates correct values for daily', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();
    Post::factory(['created_at' => now()->subDay()])
        ->count(15)
        ->create();
    Post::factory(['created_at' => now()->subDays(2)])
        ->count(7)
        ->create();
    Post::factory(['created_at' => now()->subDays(3)])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subDays(4)])
        ->count(2)
        ->create();

    $component = Livewire::test(PostsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([2, 4, 7, 15, 5]);
});

test('trend metric calculates correct values for week', function () {
    Post::factory(['created_at' => now()])
        ->count(10)
        ->create();
    Post::factory(['created_at' => now()->subWeek()])
        ->count(5)
        ->create();
    Post::factory(['created_at' => now()->subWeeks(2)])
        ->count(13)
        ->create();
    Post::factory(['created_at' => now()->subWeeks(3)])
        ->count(1)
        ->create();
    Post::factory(['created_at' => now()->subWeeks(4)])
        ->count(1)
        ->create();

    $component = Livewire::test(PostsPerWeek::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([1, 1, 13, 5, 10]);
});

test('trend metric calculates correct values for month', function () {
    Post::factory(['created_at' => now()])
        ->count(1)
        ->create();
    Post::factory(['created_at' => now()->subMonthWithoutOverflow()])
        ->count(2)
        ->create();
    Post::factory(['created_at' => now()->subMonthsWithoutOverflow(2)])
        ->count(3)
        ->create();
    Post::factory(['created_at' => now()->subMonthsWithoutOverflow(3)])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subMonthsWithoutOverflow(4)])
        ->count(5)
        ->create();

    $component = Livewire::test(PostsPerMonth::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([5, 4, 3, 2, 1]);
});

test('trend metric calculates correct values for quarter', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();
    Post::factory(['created_at' => now()->subQuarterWithoutOverflow()])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subQuartersWithoutOverflow(2)])
        ->count(3)
        ->create();
    Post::factory(['created_at' => now()->subQuartersWithoutOverflow(3)])
        ->count(2)
        ->create();
    Post::factory(['created_at' => now()->subQuartersWithoutOverflow(4)])
        ->count(1)
        ->create();

    $component = Livewire::test(PostsPerQuarter::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([1, 2, 3, 4, 5]);
});

test('trend metric calculates correct values for minutes', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();
    Post::factory(['created_at' => now()->subMinute()])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subMinutes(2)])
        ->count(3)
        ->create();
    Post::factory(['created_at' => now()->subMinutes(3)])
        ->count(2)
        ->create();
    Post::factory(['created_at' => now()->subMinutes(4)])
        ->count(1)
        ->create();

    $component = Livewire::test(PostsPerMinute::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([1, 2, 3, 4, 5]);
});

test('trend metric calculates correct values for hours', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();
    Post::factory(['created_at' => now()->subHour()])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subHours(2)])
        ->count(3)
        ->create();
    Post::factory(['created_at' => now()->subHours(3)])
        ->count(2)
        ->create();
    Post::factory(['created_at' => now()->subHours(4)])
        ->count(1)
        ->create();

    $component = Livewire::test(PostsPerHour::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([1, 2, 3, 4, 5]);
});
