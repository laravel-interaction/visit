<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Events;

use Illuminate\Support\Facades\Event;
use Zing\LaravelEloquentView\Events\Viewed;
use Zing\LaravelEloquentView\Tests\Models\Subject;
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\Tests\TestCase;

class ViewedTest extends TestCase
{
    public function testSingle(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        Event::fake();
        $user->view($subject);
        Event::assertDispatchedTimes(Viewed::class);
    }

    public function testTimes(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        Event::fake();
        $user->view($subject);
        $user->view($subject);
        Event::assertDispatchedTimes(Viewed::class, 2);
    }

    public function testWithAnonymous(): void
    {
        $subject = Subject::query()->create();
        Event::fake();
        $subject->record(request());
        Event::assertDispatchedTimes(Viewed::class);
    }
}
