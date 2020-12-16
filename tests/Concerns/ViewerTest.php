<?php

declare(strict_types=1);

namespace Zing\LaravelView\Tests\Concerns;

use Zing\LaravelView\Tests\Models\Subject;
use Zing\LaravelView\Tests\Models\User;
use Zing\LaravelView\Tests\TestCase;
use Zing\LaravelView\View;

class ViewerTest extends TestCase
{
    public function testView(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        $this->assertDatabaseHas(
            View::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'viewable_type' => $channel->getMorphClass(),
                'viewable_id' => $channel->getKey(),
            ]
        );
    }

    public function testViews(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(1, $user->views()->count());
        self::assertSame(1, $user->views->count());
    }

    public function testHasViewed(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        self::assertFalse($user->hasViewed($channel));
        $user->view($channel);
        self::assertTrue($user->hasViewed($channel));
    }

    public function testHasNotViewed(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        self::assertTrue($user->hasNotViewed($channel));
        $user->view($channel);
        self::assertFalse($user->hasNotViewed($channel));
    }
}
