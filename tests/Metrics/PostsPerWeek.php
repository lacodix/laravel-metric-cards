<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerWeek extends Trend
{
    public function value(): array
    {
        return $this->countByWeeks(Post::class);
    }
}