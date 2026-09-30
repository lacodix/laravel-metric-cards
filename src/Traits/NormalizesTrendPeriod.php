<?php

namespace Lacodix\LaravelMetricCards\Traits;

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
