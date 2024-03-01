<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Tests;

use Illuminate\Support\Carbon;
use LaravelInteraction\Visit\Tests\Models\Subject;
use LaravelInteraction\Visit\Tests\Models\User;
use LaravelInteraction\Visit\Visit;

/**
 * @internal
 */
final class VisitTest extends TestCase
{
    private User $user;

    private Subject $subject;

    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::query()->create();
        $this->subject = Subject::query()->create();
        $this->user->visit($this->subject);
        $this->visit = Visit::query()->firstOrFail();
    }

    public function testVisitTimestamp(): void
    {
        $this->assertInstanceOf(Carbon::class, $this->visit->created_at);
        $this->assertInstanceOf(Carbon::class, $this->visit->updated_at);
    }

    public function testScopeWithType(): void
    {
        $this->assertSame(1, Visit::query()->withType(Subject::class)->count());
        $this->assertSame(0, Visit::query()->withType(User::class)->count());
    }

    public function testGetTable(): void
    {
        $this->assertSame(config('visit.table_names.pivot'), $this->visit->getTable());
    }

    public function testVisitor(): void
    {
        $this->assertInstanceOf(User::class, $this->visit->visitor);
    }

    public function testVisitable(): void
    {
        $this->assertInstanceOf(Subject::class, $this->visit->visitable);
    }

    public function testUser(): void
    {
        $this->assertInstanceOf(User::class, $this->visit->user);
    }

    public function testIsVisitedTo(): void
    {
        $this->assertTrue($this->visit->isVisitedTo($this->subject));
        $this->assertFalse($this->visit->isVisitedTo($this->user));
    }

    public function testIsVisitedBy(): void
    {
        $this->assertFalse($this->visit->isVisitedBy($this->subject));
        $this->assertTrue($this->visit->isVisitedBy($this->user));
    }
}
