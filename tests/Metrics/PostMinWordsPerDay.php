<?php

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Trend;
use Tests\Models\Post;

class PostMinWordsPerDay extends Trend
{
    public function value(): array
    {
        return $this->minByDays(Post::class, 'words');
    }
}