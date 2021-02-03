<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Events;

use Illuminate\Database\Eloquent\Model;

class Viewed
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $view;

    /**
     * Viewed constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $view
     */
    public function __construct(Model $view)
    {
        $this->view = $view;
    }
}
