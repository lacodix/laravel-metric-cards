<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostAvgWordsPerDay extends Trend
{
    public function value(): array
    {
        return $this->avgByDays(Post::class, 'words');
    }
}