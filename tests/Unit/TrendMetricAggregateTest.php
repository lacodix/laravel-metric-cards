<?php

use Livewire\Livewire;
use Tests\Metrics\PostAvgWordsPerDay;
use Tests\Metrics\PostMaxWordsPerDay;
use Tests\Metrics\PostMinWordsPerDay;
use Tests\Metrics\PostSumWordsPerDay;
use Tests\Models\Post;

test('trend metric calculates correct max values for daily', function () {
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight')])
        ->count(5)
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 5])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 105])
        ->create();

    $component = Livewire::test(PostMaxWordsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 105]);
});

test('trend metric calculates correct min values for daily', function () {
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight')])
        ->count(5)
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 5])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 105])
        ->create();

    $component = Livewire::test(PostMinWordsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 5]);
});

test('trend metric calculates correct avg values for daily', function () {
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 50])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 7])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 105])
        ->create();

    $component = Livewire::test(PostAvgWordsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 54]);
});

test('trend metric calculates correct sum values for daily', function () {
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 50])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 5])
        ->create();
    Post::factory(['created_at' => fake()->dateTimeBetween('midnight'), 'words' => 105])
        ->create();

    $component = Livewire::test(PostSumWordsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 160]);
});
