<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit;

use LaravelInteraction\Support\InteractionList;
use LaravelInteraction\Support\InteractionServiceProvider;

class VisitServiceProvider extends InteractionServiceProvider
{
    /**
     * @var string
     */
    protected $interaction = InteractionList::VISIT;
}
