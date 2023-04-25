<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerQuarter extends Trend
{
    public function value(): array
    {
        return $this->countByQuarters(Post::class);
    }
}