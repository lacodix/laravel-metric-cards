<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerHour extends Trend
{
    public function value(): array
    {
        return $this->countByHours(Post::class);
    }
}