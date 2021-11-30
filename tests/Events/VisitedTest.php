<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests\Events;

use Illuminate\Support\Facades\Event;
use LaravelInteraction\Visit\Events\Visited;
use LaravelInteraction\Visit\Tests\Models\Subject;
use LaravelInteraction\Visit\Tests\Models\User;
use LaravelInteraction\Visit\Tests\TestCase;

/**
 * @internal
 */
final class VisitedTest extends TestCase
{
    public function testSingle(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        Event::fake();
        $user->visit($subject);
        Event::assertDispatchedTimes(Visited::class);
    }

    public function testTimes(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        Event::fake();
        $user->visit($subject);
        $user->visit($subject);
        Event::assertDispatchedTimes(Visited::class, 2);
    }

    public function testWithAnonymous(): void
    {
        $subject = Subject::query()->create();
        Event::fake();
        $subject->record(request());
        Event::assertDispatchedTimes(Visited::class);
    }
}
