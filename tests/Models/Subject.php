<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentView\Concerns\Viewable;

/**
 * @method static \Zing\LaravelEloquentView\Tests\Models\Subject|\Illuminate\Database\Eloquent\Builder query()
 */
class Subject extends Model
{
    use Viewable;
}
