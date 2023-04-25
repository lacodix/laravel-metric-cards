<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerMonth extends Trend
{
    public function value(): array
    {
        return $this->countByMonths(Post::class);
    }
}