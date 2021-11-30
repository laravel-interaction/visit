<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Visit\Concerns\Visitable;

/**
 * @method static \LaravelInteraction\Visit\Tests\Models\Subject|\Illuminate\Database\Eloquent\Builder query()
 */
class Subject extends Model
{
    use Visitable;
}
