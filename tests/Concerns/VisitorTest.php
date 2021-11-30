<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests\Concerns;

use LaravelInteraction\Visit\Tests\Models\Subject;
use LaravelInteraction\Visit\Tests\Models\User;
use LaravelInteraction\Visit\Tests\TestCase;
use LaravelInteraction\Visit\Visit;

/**
 * @internal
 */
final class VisitorTest extends TestCase
{
    public function testVisit(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $this->assertDatabaseHas(
            Visit::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'visitable_type' => $subject->getMorphClass(),
                'visitable_id' => $subject->getKey(),
            ]
        );
    }

    public function testVisits(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame(1, $user->visitVisitors()->count());
        self::assertSame(1, $user->visitVisitors->count());
    }

    public function testHasVisited(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertFalse($user->hasVisited($subject));
        $user->visit($subject);
        self::assertTrue($user->hasVisited($subject));
    }

    public function testHasNotVisited(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertTrue($user->hasNotVisited($subject));
        $user->visit($subject);
        self::assertFalse($user->hasNotVisited($subject));
    }
}
