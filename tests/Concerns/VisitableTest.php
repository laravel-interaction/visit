<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests\Concerns;

use LaravelInteraction\Visit\Tests\Models\Subject;
use LaravelInteraction\Visit\Tests\Models\User;
use LaravelInteraction\Visit\Tests\TestCase;

/**
 * @internal
 */
final class VisitableTest extends TestCase
{
    public function testVisits(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame(1, $subject->visitableVisits()->count());
        self::assertSame(1, $subject->visitableVisits->count());
    }

    public function testVisitorsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertSame(0, $subject->visitorsCount());
        $user->visit($subject);
        $subject->loadVisitorsCount();
        self::assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        $subject->loadVisitorsCount();
        self::assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        self::assertSame(1, $subject->visitors()->count());
        self::assertSame(1, $subject->visitors->count());
        $paginate = $subject->visitors()
            ->paginate();
        self::assertSame(1, $paginate->total());
        self::assertCount(1, $paginate->items());
        $subject->loadVisitorsCount(static fn ($query) => $query->whereKeyNot($user->getKey()));
        self::assertSame(0, $subject->visitorsCount());
        $user2 = User::query()->create();
        $user2->visit($subject);

        $subject->loadVisitorsCount();
        self::assertSame(2, $subject->visitorsCount());
        self::assertSame(2, $subject->visitors()->count());
        $subject->load('visitors');
        self::assertSame(2, $subject->visitors->count());
        $paginate = $subject->visitors()
            ->paginate();
        self::assertSame(2, $paginate->total());
        self::assertCount(2, $paginate->items());
    }

    public function testWithVisitorsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertSame(0, $subject->visitorsCount());
        $user->visit($subject);
        $subject = Subject::query()->withVisitorsCount()->whereKey($subject->getKey())->firstOrFail();
        self::assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        $subject = Subject::query()->withVisitorsCount()->whereKey($subject->getKey())->firstOrFail();
        self::assertSame(1, $subject->visitorsCount());
        $subject = Subject::query()->withVisitorsCount(
            static fn ($query) => $query->whereKeyNot($user->getKey())
        )->whereKey($subject->getKey())
            ->firstOrFail();

        self::assertSame(0, $subject->visitorsCount());
    }

    public function testVisitsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame(1, $subject->visitsCount());
        $user->visit($subject);
        $subject->loadCount('visitableVisits');
        self::assertSame(2, $subject->visitsCount());
        self::assertSame('2', $subject->visitsCountForHumans());
    }

    public function testVisitorsCountForHumans(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame('1', $subject->visitorsCountForHumans());
    }

    public function testIsVisitedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertFalse($subject->isVisitedBy($subject));
        $user->visit($subject);
        self::assertTrue($subject->isVisitedBy($user));
        $subject->load('visitors');
        self::assertTrue($subject->isVisitedBy($user));
    }

    public function testIsNotVisitedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        self::assertTrue($subject->isNotVisitedBy($subject));
        $user->visit($subject);
        self::assertFalse($subject->isNotVisitedBy($user));
        $subject->load('visitors');
    }

    public function testVisitors(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $user->visit($subject);
        self::assertSame(1, $subject->visitors->count());
    }

    public function testScopeWhereVisitedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame(1, Subject::query()->whereVisitedBy($user)->count());
        self::assertSame(0, Subject::query()->whereVisitedBy($other)->count());
    }

    public function testScopeWhereNotVisitedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        self::assertSame(0, Subject::query()->whereNotVisitedBy($user)->count());
        self::assertSame(1, Subject::query()->whereNotVisitedBy($other)->count());
    }

    public function testRecord(): void
    {
        $subject = Subject::query()->create();
        $subject->record(request());
        self::assertSame(1, $subject->visitsCount());
        $user = User::query()->create();
        request()
            ->setUserResolver(static fn (): \LaravelInteraction\Visit\Tests\Models\User => $user);
        $subject->record(request());
        $subject->loadCount('visitableVisits');
        self::assertSame(1, $subject->visitorsCount());
        self::assertSame(2, $subject->visitsCount());
        self::assertTrue($subject->isVisitedBy($user));
    }
}
