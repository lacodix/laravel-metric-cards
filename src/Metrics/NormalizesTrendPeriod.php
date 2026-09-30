<?php

namespace Lacodix\LaravelMetricCards\Metrics;

trait NormalizesTrendPeriod
{
    public function hydrateNormalizesTrendPeriod(): void
    {
        $this->normalizePeriod();
    }

    public function updatedNormalizesTrendPeriod($path, $value): void
    {
        if ($path === 'period') {
            $this->normalizePeriod();
        }
    }
}
