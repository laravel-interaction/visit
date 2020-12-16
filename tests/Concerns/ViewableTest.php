<?php

declare(strict_types=1);

namespace Zing\LaravelView\Tests\Concerns;

use Mockery;
use Zing\LaravelView\Tests\Models\Subject;
use Zing\LaravelView\Tests\Models\User;
use Zing\LaravelView\Tests\TestCase;

class ViewableTest extends TestCase
{
    public function testViews(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(1, $channel->views()->count());
        self::assertSame(1, $channel->views->count());
    }

    public function testViewersCount(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        self::assertSame(0, $channel->viewersCount());
        $user->view($channel);
        $channel->loadViewersCount();
        self::assertSame(1, $channel->viewersCount());
        $user->view($channel);
        $channel->loadViewersCount();
        self::assertSame(1, $channel->viewersCount());
    }

    public function testViewsCount(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(1, $channel->viewsCount());
        $user->view($channel);
        $channel->loadCount('views');
        self::assertSame(2, $channel->viewsCount());
        self::assertSame('2', $channel->viewsCountForHumans());
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
        $channel = Mockery::mock(Subject::class);
        $channel->shouldReceive('viewersCountForHumans')->passthru();
        $channel->shouldReceive('viewersCount')->andReturn($actual);
        self::assertSame($onePrecision, $channel->viewersCountForHumans());
        self::assertSame($twoPrecision, $channel->viewersCountForHumans(2));
        self::assertSame($halfDown, $channel->viewersCountForHumans(2, PHP_ROUND_HALF_DOWN));
    }

    public function testIsViewedBy(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        self::assertFalse($channel->isViewedBy($channel));
        $user->view($channel);
        self::assertTrue($channel->isViewedBy($user));
        $channel->load('viewers');
    }

    public function testIsNotViewedBy(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        self::assertTrue($channel->isNotViewedBy($channel));
        $user->view($channel);
        self::assertFalse($channel->isNotViewedBy($user));
        $channel->load('viewers');
    }

    public function testViewers(): void
    {
        $user = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(1, $channel->viewers()->count());
    }

    public function testScopeWhereViewedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(1, Subject::query()->whereViewedBy($user)->count());
        self::assertSame(0, Subject::query()->whereViewedBy($other)->count());
    }

    public function testScopeWhereNotViewedBy(): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $channel = Subject::query()->create();
        $user->view($channel);
        self::assertSame(0, Subject::query()->whereNotViewedBy($user)->count());
        self::assertSame(1, Subject::query()->whereNotViewedBy($other)->count());
    }
}
