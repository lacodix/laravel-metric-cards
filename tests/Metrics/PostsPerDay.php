<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerDay extends Trend
{
    public function value(): array
    {
        return $this->countByDays(Post::class);
    }
}