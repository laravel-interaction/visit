# Laravel Visit

User visit behaviour for Laravel.

<p align="center">
<a href="https://packagist.org/packages/laravel-interaction/visit"><img src="https://poser.pugx.org/laravel-interaction/visit/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/visit"><img src="https://poser.pugx.org/laravel-interaction/visit/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel-interaction/visit"><img src="https://poser.pugx.org/laravel-interaction/visit/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/visit"><img src="https://poser.pugx.org/laravel-interaction/visit/license" alt="License"></a>
</p>

## Introduction

It used to record the number of visits to the model(documentation/subject/question).

![](https://img.shields.io/badge/%F0%9F%93%96-1.2k-green?style=social)

## Installation

### Requirements

- [PHP 7.3+](https://php.net/releases/)
- [Composer](https://getcomposer.org)
- [Laravel 8.0+](https://laravel.com/docs/releases)

### Instructions

Require Laravel Visit using [Composer](https://getcomposer.org).

```bash
composer require laravel-interaction/visit
```

Publish configuration and migrations

```bash
php artisan vendor:publish --tag=visit-config
php artisan vendor:publish --tag=visit-migrations
```

Run database migrations.

```bash
php artisan migrate
```

## Usage

### Setup Visitor

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Visit\Concerns\Visitor;

class User extends Model
{
    use Visitor;
}
```

### Setup Visitable

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Visit\Concerns\Visitable;

class Subject extends Model
{
    use Visitable;
}
```

### Visitor

```php
use LaravelInteraction\Visit\Tests\Models\Subject;
/** @var \LaravelInteraction\Visit\Tests\Models\User $user */
/** @var \LaravelInteraction\Visit\Tests\Models\Subject $subject */
// Visit to Visitable
$user->visit($subject);

// Compare Visitable
$user->hasVisited($subject);
$user->hasNot
Visited($subject);

// Get visited info
$user->visitVisitors()->count(); 

// with type
$user->visitVisitors()->withType(Subject::class)->count(); 

// get visited subjects
Subject::query()->whereVisitedBy($user)->get();

// get subjects doesnt visited
Subject::query()->whereNotVisitedBy($user)->get();
```

### Visitable

```php
use LaravelInteraction\Visit\Tests\Models\User;
use LaravelInteraction\Visit\Tests\Models\Subject;
/** @var \LaravelInteraction\Visit\Tests\Models\User $user */
/** @var \LaravelInteraction\Visit\Tests\Models\Subject $subject */
// Compare Visitor
$subject->isVisitedBy($user); 
$subject->isNotVisitedBy($user);
// Get visitors info
$subject->visitors->each(function (User $user){
    echo $user->getKey();
});

$subjects = Subject::query()->withVisitorsCount()->get();
$subjects->each(function (Subject $subject){
    // like uv
    echo $subject->visitors()->count(); // 1100
    echo $subject->visitors_count; // "1100"
    echo $subject->visitorsCount(); // 1100
    echo $subject->visitorsCountForHumans(); // "1.1K"
    // like pv
    echo $subject->visitableVisits()->count(); // 1100
    echo $subject->visits_count; // "1100"
    echo $subject->visitsCount(); // 1100
    echo $subject->visitsCountForHumans(); // "1.1K"
});
$subjects = Subject::query()->withVisitorsCount(function ($query){
    return $query->whereKey(1);
})->get();
```

## With Api Request

```php
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelInteraction\Visit\Tests\Models\Subject;
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
| `LaravelInteraction\Visit\Events\Visited` | When an object get visited. |

## License

Laravel Eloquent Visit is an open-sourced software licensed under the [MIT license](LICENSE).
