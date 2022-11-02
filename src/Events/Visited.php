<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Events;

use Illuminate\Database\Eloquent\Model;

class Visited
{
    public function __construct(public Model $model)
    {
    }
}
