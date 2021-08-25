<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentView\Concerns\Viewer;

/**
 * @method static \Zing\LaravelEloquentView\Tests\Models\User|\Illuminate\Database\Eloquent\Builder query()
 */
class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    use Viewer;
}
