<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Concerns;

use Zing\LaravelEloquentView\Tests\Models\Subject;
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\Tests\TestCase;

class ViewableTest extends TestCase
{
    public function testViews(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(1, $subject->views()->count());
        self::assertSame(1, $subject->views->count());
    }

    public function testViewersCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertSame(0, $subject->viewersCount());
        $user->view($subject);
        $subject->loadViewersCount();
        self::assertSame(1, $subject->viewersCount());
        $user->view($subject);
        $subject->loadViewersCount();
        self::assertSame(1, $subject->viewersCount());
    }

    public function testViewsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(1, $subject->viewsCount());
        $user->view($subject);
        $subject->loadCount('views');
        self::assertSame(2, $subject->viewsCount());
        self::assertSame('2', $subject->viewsCountForHumans());
    }

    public function testViewersCountForHumans(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame('1', $subject->viewsCountForHumans());
    }

    public function testIsViewedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertFalse($subject->isViewedBy($subject));
        $user->view($subject);
        self::assertTrue($subject->isViewedBy($user));
        $subject->load('viewers');
    }

    public function testIsNotViewedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertTrue($subject->isNotViewedBy($subject));
        $user->view($subject);
        self::assertFalse($subject->isNotViewedBy($user));
        $subject->load('viewers');
    }

    public function testViewers(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(1, $subject->viewers()->count());
    }

    public function testScopeWhereViewedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(1, Subject::query()->whereViewedBy($user)->count());
        self::assertSame(0, Subject::query()->whereViewedBy($other)->count());
    }

    public function testScopeWhereNotViewedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(0, Subject::query()->whereNotViewedBy($user)->count());
        self::assertSame(1, Subject::query()->whereNotViewedBy($other)->count());
    }

    public function testRecord(): void
    {
        $subject = Subject::query()->create();
        $subject->record(request());
        self::assertSame(1, $subject->viewsCount());
        $user = User::query()->create();
        request()->setUserResolver(
            function () use ($user) {
                return $user;
            }
        );
        $subject->record(request());
        $subject->loadCount('views');
        self::assertSame(1, $subject->viewersCount());
        self::assertSame(2, $subject->viewsCount());
        self::assertTrue($subject->isViewedBy($user));
    }
}
