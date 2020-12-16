# Laravel View

User view behaviour for Laravel.

<p align="center">
<a href="https://github.com/zingimmick/laravel-view/actions"><img src="https://github.com/zingimmick/laravel-view/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/zingimmick/laravel-view"><img src="https://codecov.io/gh/zingimmick/laravel-view/branch/master/graph/badge.svg" alt="Code Coverage" /></a>
<a href="https://packagist.org/packages/zing/laravel-view"><img src="https://poser.pugx.org/zing/laravel-view/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-view"><img src="https://poser.pugx.org/zing/laravel-view/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/zing/laravel-view"><img src="https://poser.pugx.org/zing/laravel-view/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-view"><img src="https://poser.pugx.org/zing/laravel-view/license" alt="License"></a>
<a href="https://codeclimate.com/github/zingimmick/laravel-view/maintainability"><img src="https://api.codeclimate.com/v1/badges/82036f5ecf894e9c395d/maintainability" alt="Code Climate" /></a>
</p>

> **Requires [PHP 7.2.0+](https://php.net/releases/)**

Require Laravel View using [Composer](https://getcomposer.org):

```bash
composer require zing/laravel-view
```

## Usage

### Setup Viewer

```php
use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentView\Concerns\Viewer;

class User extends Model
{
    use Viewer;
}
```

### Setup Viewable

```php
use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentView\Concerns\Viewable;

class Subject extends Model
{
    use Viewable;
}
```

### Viewer

```php
use Zing\LaravelEloquentView\Tests\Models\Subject;
/** @var \Zing\LaravelEloquentView\Tests\Models\User $user */
/** @var \Zing\LaravelEloquentView\Tests\Models\Subject $channel */
// View to Viewable
$user->view($channel);

// Compare Viewable
$user->hasViewed($channel);
$user->hasNotViewed($channel);

// Get subscribed info
$user->views()->count(); 

// with type
$user->views()->withType(Subject::class)->count(); 

// get subscribed channels
Subject::query()->whereViewedBy($user)->get();

// get subscribed channels doesnt subscribed
Subject::query()->whereNotViewedBy($user)->get();
```

### Viewable

```php
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\Tests\Models\Subject;
/** @var \Zing\LaravelEloquentView\Tests\Models\User $user */
/** @var \Zing\LaravelEloquentView\Tests\Models\Subject $channel */
// Compare Viewer
$channel->isViewedBy($user); 
$channel->isNotViewedBy($user);
// Get subscribers info
$channel->viewers->each(function (User $user){
    echo $user->getKey();
});

$channels = Subject::query()->withCount('subscribers')->get();
$channels->each(function (Subject $channel){
    // like uv
    echo $channel->viewers()->count(); // 1100
    echo $channel->viewers_count; // "1100"
    echo $channel->viewersCount(); // 1100
    echo $channel->viewersCountForHumans(); // "1.1K"
    // like pv
    echo $channel->views()->count(); // 1100
    echo $channel->views_count; // "1100"
    echo $channel->viewsCount(); // 1100
    echo $channel->viewsCountForHumans(); // "1.1K"
});
```

## License

Laravel View is an open-sourced software licensed under the [MIT license](LICENSE).
