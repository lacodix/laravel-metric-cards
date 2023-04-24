<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lacodix\LaravelModelFilter\Enums\FilterMode;
use Lacodix\LaravelModelFilter\Enums\ValidationMode;
use Lacodix\LaravelModelFilter\Filters\BooleanFilter;
use Lacodix\LaravelModelFilter\Filters\DateFilter;
use Lacodix\LaravelModelFilter\Filters\NumericFilter;
use Lacodix\LaravelModelFilter\Filters\StringFilter;
use Lacodix\LaravelModelFilter\Traits\HasFilters;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;
use Tests\Filters\CounterFilter;
use Tests\Filters\IndividualFilter;
use Tests\Filters\TypeFilter;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];
}
