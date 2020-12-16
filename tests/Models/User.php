<?php

declare(strict_types=1);

namespace Zing\LaravelView\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelView\Concerns\Viewer;

/**
 * @method static \Zing\LaravelView\Tests\Models\User|\Illuminate\Database\Eloquent\Builder query()
 */
class User extends Model
{
    use Viewer;
}
