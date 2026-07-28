<?php

declare(strict_types=1);

namespace Tests\Metrics;

use Lacodix\LaravelMetricCards\Metrics\Pie;

/**
 * A pie metric whose number of segments comes from outside - the case the
 * color resolution exists for. Real metrics get that number from data (one
 * segment per status, per age group, ...), a test gets it from a property.
 */
class PostsPerGroup extends Pie
{
    public int $groups = 3;

    public function value(): array
    {
        $values = [];

        for ($group = 1; $group <= $this->groups; $group++) {
            $values['Group ' . $group] = $group;
        }

        return $values;
    }
}
