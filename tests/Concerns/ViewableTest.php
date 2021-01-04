<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests\Concerns;

use Mockery;
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

    public function data(): array
    {
        return [
            [0, '0', '0', '0'],
            [1, '1', '1', '1'],
            [12, '12', '12', '12'],
            [123, '123', '123', '123'],
            [12345, '12.3K', '12.35K', '12.34K'],
            [1234567, '1.2M', '1.23M', '1.23M'],
            [123456789, '123.5M', '123.46M', '123.46M'],
            [12345678901, '12.3B', '12.35B', '12.35B'],
            [1234567890123, '1.2T', '1.23T', '1.23T'],
            [1234567890123456, '1.2Qa', '1.23Qa', '1.23Qa'],
            [1234567890123456789, '1.2Qi', '1.23Qi', '1.23Qi'],
        ];
    }

    /**
     * @dataProvider data
     *
     * @param mixed $actual
     * @param mixed $onePrecision
     * @param mixed $twoPrecision
     * @param mixed $halfDown
     */
    public function testViewersCountForHumans($actual, $onePrecision, $twoPrecision, $halfDown): void
    {
        $legacyMock = Mockery::mock(Subject::class);
        $legacyMock->shouldReceive('viewersCountForHumans')->passthru();
        $legacyMock->shouldReceive('viewersCount')->andReturn($actual);
        self::assertSame($onePrecision, $legacyMock->viewersCountForHumans());
        self::assertSame($twoPrecision, $legacyMock->viewersCountForHumans(2));
        self::assertSame($halfDown, $legacyMock->viewersCountForHumans(2, PHP_ROUND_HALF_DOWN));
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
