<?php

use Livewire\Livewire;
use Tests\Metrics\NewPosts;
use Tests\Models\Post;

test('value metric shows correct values', function () {
    Post::factory(['created_at' => now()])
        ->count(5)
        ->create();

    Post::factory(['created_at' => now()->subDay()->subHour()])
        ->count(15)
        ->create();

    Post::factory(['created_at' => now()->subWeek()->subHour()])
        ->count(20)
        ->create();

    Post::factory(['created_at' => now()->subDays(35)->subHour()])
        ->count(20)
        ->create();

    $component = Livewire::test(NewPosts::class)
        ->set('period', '1')
        ->assertSee('trending-down')
        ->assertSee('fill-red-500');

    expect($component->get('previousValue'))->toEqual(15)
        ->and($component->get('currentValue'))->toEqual(5)
        ->and($component->get('changePercentage'))->toEqual(-66.67);

    $component->set('period', '7')
        ->assertSee('trending-up')
        ->assertDontSee('fill-green-500')
        ->assertDontSee('fill-red-500');

    expect($component->get('previousValue'))->toEqual(20)
        ->and($component->get('currentValue'))->toEqual(20)
        ->and($component->get('changePercentage'))->toEqual(0);

    $component->set('period', '30')
        ->assertSee('trending-up')
        ->assertSee('fill-green-500');

    expect($component->get('previousValue'))->toEqual(20)
        ->and($component->get('currentValue'))->toEqual(40)
        ->and($component->get('changePercentage'))->toEqual(100);
});
