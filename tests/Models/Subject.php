<?php

declare(strict_types=1);

namespace Zing\LaravelView\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelView\Concerns\Viewable;

/**
 * @method static \Zing\LaravelView\Tests\Models\Subject|\Illuminate\Database\Eloquent\Builder query()
 */
class Subject extends Model
{
    use Viewable;
}
