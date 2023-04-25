<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostSumWordsPerDay extends Trend
{
    public function value(): array
    {
        return $this->sumByDays(Post::class, 'words');
    }
}