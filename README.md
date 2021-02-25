# Laravel Eloquent View

User view behaviour for Laravel.

<p align="center">
<a href="https://github.com/zingimmick/laravel-eloquent-view/actions"><img src="https://github.com/zingimmick/laravel-eloquent-view/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/zingimmick/laravel-eloquent-view"><img src="https://codecov.io/gh/zingimmick/laravel-eloquent-view/branch/master/graph/badge.svg" alt="Code Coverage" /></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-view"><img src="https://poser.pugx.org/zing/laravel-eloquent-view/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-view"><img src="https://poser.pugx.org/zing/laravel-eloquent-view/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-view"><img src="https://poser.pugx.org/zing/laravel-eloquent-view/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-view"><img src="https://poser.pugx.org/zing/laravel-eloquent-view/license" alt="License"></a>
<a href="https://codeclimate.com/github/zingimmick/laravel-eloquent-view/maintainability"><img src="https://api.codeclimate.com/v1/badges/fecfe975a2ed45335e1c/maintainability" alt="Code Climate" /></a>
</p>

> **Requires [PHP 7.2.0+](https://php.net/releases/)**

Require Laravel Eloquent View using [Composer](https://getcomposer.org):

```bash
composer require zing/laravel-eloquent-view
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
/** @var \Zing\LaravelEloquentView\Tests\Models\Subject $subject */
// View to Viewable
$user->view($subject);

// Compare Viewable
$user->hasViewed($subject);
$user->hasNotViewed($subject);

// Get viewed info
$user->views()->count(); 

// with type
$user->views()->withType(Subject::class)->count(); 

// get viewed subjects
Subject::query()->whereViewedBy($user)->get();

// get subjects doesnt viewed
Subject::query()->whereNotViewedBy($user)->get();
```

### Viewable

```php
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\Tests\Models\Subject;
/** @var \Zing\LaravelEloquentView\Tests\Models\User $user */
/** @var \Zing\LaravelEloquentView\Tests\Models\Subject $subject */
// Compare Viewer
$subject->isViewedBy($user); 
$subject->isNotViewedBy($user);
// Get viewers info
$subject->viewers->each(function (User $user){
    echo $user->getKey();
});

$subjects = Subject::query()->withViewersCount()->get();
$subjects->each(function (Subject $subject){
    // like uv
    echo $subject->viewers()->count(); // 1100
    echo $subject->viewers_count; // "1100"
    echo $subject->viewersCount(); // 1100
    echo $subject->viewersCountForHumans(); // "1.1K"
    // like pv
    echo $subject->views()->count(); // 1100
    echo $subject->views_count; // "1100"
    echo $subject->viewsCount(); // 1100
    echo $subject->viewsCountForHumans(); // "1.1K"
});
$subjects = Subject::query()->withViewersCount(function ($query){
    return $query->whereKey(1);
})->get();
```

## With Api Request

```php
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Zing\LaravelEloquentView\Tests\Models\Subject;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionController extends Controller
{
    public function show($id, Request $request){
        $subject = Subject::query()->findOrFail($id);
        dispatch(function () use ($subject, $request) {
            $subject->record($request);
        })->afterResponse();
        return new JsonResource($subject);
    }
}
```

### Events

| Event | Fired |
| --- | --- |
| `Zing\LaravelEloquentView\Events\Viewed` | When an object get viewed. |

## License

Laravel Eloquent View is an open-sourced software licensed under the [MIT license](LICENSE).
