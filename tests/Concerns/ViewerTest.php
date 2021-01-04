<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Concerns;

use Zing\LaravelEloquentView\Tests\Models\Subject;
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\Tests\TestCase;
use Zing\LaravelEloquentView\View;

class ViewerTest extends TestCase
{
    public function testView(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        $this->assertDatabaseHas(
            View::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'viewable_type' => $subject->getMorphClass(),
                'viewable_id' => $subject->getKey(),
            ]
        );
    }

    public function testViews(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->view($subject);
        self::assertSame(1, $user->views()->count());
        self::assertSame(1, $user->views->count());
    }

    public function testHasViewed(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertFalse($user->hasViewed($subject));
        $user->view($subject);
        self::assertTrue($user->hasViewed($subject));
    }

    public function testHasNotViewed(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertTrue($user->hasNotViewed($subject));
        $user->view($subject);
        self::assertFalse($user->hasNotViewed($subject));
    }
}
