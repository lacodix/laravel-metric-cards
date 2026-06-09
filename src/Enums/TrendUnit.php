<?php

declare(strict_types=1);

namespace Lacodix\LaravelMetricCards\Enums;

enum TrendUnit: string
{
    case QUARTER = 'quarter';
    case MONTH = 'month';
    case WEEK = 'week';
    case DAY = 'day';
    case HOUR = 'hour';
    case MINUTE = 'minute';
}
