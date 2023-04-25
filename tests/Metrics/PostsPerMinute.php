<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostsPerMinute extends Trend
{
    public function value(): array
    {
        return $this->countByMinutes(Post::class);
    }
}