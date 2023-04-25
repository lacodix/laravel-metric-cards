<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostMaxWordsPerDay extends Trend
{
    public function value(): array
    {
        return $this->maxByDays(Post::class, 'words');
    }
}