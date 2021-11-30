<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Visit\Concerns\Visitor;

/**
 * @method static \LaravelInteraction\Visit\Tests\Models\User|\Illuminate\Database\Eloquent\Builder query()
 */
class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;
    use Visitor;
}
