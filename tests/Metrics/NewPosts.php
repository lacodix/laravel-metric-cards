<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Value;
use Tests\Models\Post;

class NewPosts extends Value
{
    public function value(): array
    {
        return $this->count(Post::class);
    }
}