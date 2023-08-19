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
        $this->assertSame(1, $subject->visitableVisits()->count());
        $this->assertSame(1, $subject->visitableVisits->count());
    }

    public function testVisitorsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $this->assertSame(0, $subject->visitorsCount());
        $user->visit($subject);
        $subject->loadVisitorsCount();
        $this->assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        $subject->loadVisitorsCount();
        $this->assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        $this->assertSame(1, $subject->visitors()->count());
        $this->assertSame(1, $subject->visitors->count());
        $paginate = $subject->visitors()
            ->paginate();
        $this->assertSame(1, $paginate->total());
        $this->assertCount(1, $paginate->items());
        $subject->loadVisitorsCount(static fn ($query) => $query->whereKeyNot($user->getKey()));
        $this->assertSame(0, $subject->visitorsCount());
        $user2 = User::query()->create();
        $user2->visit($subject);

        $subject->loadVisitorsCount();
        $this->assertSame(2, $subject->visitorsCount());
        $this->assertSame(2, $subject->visitors()->count());
        $subject->load('visitors');
        $this->assertSame(2, $subject->visitors->count());
        $paginate = $subject->visitors()
            ->paginate();
        $this->assertSame(2, $paginate->total());
        $this->assertCount(2, $paginate->items());
    }

    public function testWithVisitorsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $this->assertSame(0, $subject->visitorsCount());
        $user->visit($subject);
        $subject = Subject::query()->withVisitorsCount()->whereKey($subject->getKey())->firstOrFail();
        $this->assertSame(1, $subject->visitorsCount());
        $user->visit($subject);
        $subject = Subject::query()->withVisitorsCount()->whereKey($subject->getKey())->firstOrFail();
        $this->assertSame(1, $subject->visitorsCount());
        $subject = Subject::query()->withVisitorsCount(
            static fn ($query) => $query->whereKeyNot($user->getKey())
        )->whereKey($subject->getKey())
            ->firstOrFail();

        $this->assertSame(0, $subject->visitorsCount());
    }

    public function testVisitsCount(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $this->assertSame(1, $subject->visitsCount());
        $user->visit($subject);
        $subject->loadCount('visitableVisits');
        $this->assertSame(2, $subject->visitsCount());
        $this->assertSame('2', $subject->visitsCountForHumans());
    }

    public function testVisitorsCountForHumans(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $this->assertSame('1', $subject->visitorsCountForHumans());
    }

    public function testIsVisitedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $this->assertFalse($subject->isVisitedBy($subject));
        $user->visit($subject);
        $this->assertTrue($subject->isVisitedBy($user));
        $subject->load('visitors');
        $this->assertTrue($subject->isVisitedBy($user));
    }

    public function testIsNotVisitedBy(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $this->assertTrue($subject->isNotVisitedBy($subject));
        $user->visit($subject);
        $this->assertFalse($subject->isNotVisitedBy($user));
        $subject->load('visitors');
    }

    public function testVisitors(): void
    {
        $user = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $user->visit($subject);
        $this->assertSame(1, $subject->visitors->count());
    }

    public function testScopeWhereVisitedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $this->assertSame(1, Subject::query()->whereVisitedBy($user)->count());
        $this->assertSame(0, Subject::query()->whereVisitedBy($other)->count());
    }

    public function testScopeWhereNotVisitedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $subject = Subject::query()->create();
        $user->visit($subject);
        $this->assertSame(0, Subject::query()->whereNotVisitedBy($user)->count());
        $this->assertSame(1, Subject::query()->whereNotVisitedBy($other)->count());
    }

    public function testRecord(): void
    {
        $subject = Subject::query()->create();
        $subject->record(request());
        $this->assertSame(1, $subject->visitsCount());
        $user = User::query()->create();
        request()
            ->setUserResolver(static fn (): \LaravelInteraction\Visit\Tests\Models\User => $user);
        $subject->record(request());
        $subject->loadCount('visitableVisits');
        $this->assertSame(1, $subject->visitorsCount());
        $this->assertSame(2, $subject->visitsCount());
        $this->assertTrue($subject->isVisitedBy($user));
    }
}
