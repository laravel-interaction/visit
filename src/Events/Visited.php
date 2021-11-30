<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Events;

use Illuminate\Database\Eloquent\Model;

class Visited
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $visit;

    public function __construct(Model $visit)
    {
        $this->visit = $visit;
    }
}
