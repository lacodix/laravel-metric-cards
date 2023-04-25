<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(random_int(5, 15), true),
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'words' => rand(10, 100),
        ];
    }
}
