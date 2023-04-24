<?php

use Livewire\Livewire;
use Tests\Metrics\NewPosts;
use Tests\Metrics\PostsPerDay;
use Tests\Models\Post;

test('true is true', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();

    Post::factory(['created_at' => now()->subDay()->subHour()])
        ->count(15)
        ->create();

    $component = Livewire::test(NewPosts::class)
        ->set('period', '1');

    expect($component->get('previousValue'))->toEqual(15)
        ->and($component->get('currentValue'))->toEqual(5)
        ->and($component->get('changePercentage'))->toEqual(-66.67);
});

test('test the trend', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();

    Post::factory(['created_at' => now()->subDay()->subHour()])
        ->count(15)
        ->create();

    Post::factory(['created_at' => now()->subDays(2)->subHour()])
        ->count(7)
        ->create();
    Post::factory(['created_at' => now()->subDays(3)->subHour()])
        ->count(4)
        ->create();
    Post::factory(['created_at' => now()->subDays(4)->subHour()])
        ->count(2)
        ->create();

    $component = Livewire::test(PostsPerDay::class)
        ->set('period', '5');

    expect(array_values($component->get('values')))->toEqual([2, 4, 7, 15, 5]);
});