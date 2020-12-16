<?php

declare(strict_types=1);

namespace Zing\LaravelView\Tests;

use Illuminate\Support\Carbon;
use Zing\LaravelView\Tests\Models\Subject;
use Zing\LaravelView\Tests\Models\User;
use Zing\LaravelView\View;

class ViewTest extends TestCase
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Zing\LaravelView\Tests\Models\User
     */
    protected $user;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Zing\LaravelView\Tests\Models\Subject
     */
    protected $channel;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|\Zing\LaravelView\View|null
     */
    protected $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::query()->create();
        $this->channel = Subject::query()->create();
        $this->user->view($this->channel);
        $this->view = View::query()->first();
    }

    public function testViewTimestamp(): void
    {
        self::assertInstanceOf(Carbon::class, $this->view->created_at);
        self::assertInstanceOf(Carbon::class, $this->view->updated_at);
    }

    public function testScopeWithType(): void
    {
        self::assertSame(1, View::query()->withType(Subject::class)->count());
        self::assertSame(0, View::query()->withType(User::class)->count());
    }

    public function testGetTable(): void
    {
        self::assertSame(config('view.table_names.views'), $this->view->getTable());
    }

    public function testViewer(): void
    {
        self::assertInstanceOf(User::class, $this->view->viewer);
    }

    public function testViewable(): void
    {
        self::assertInstanceOf(Subject::class, $this->view->viewable);
    }

    public function testUser(): void
    {
        self::assertInstanceOf(User::class, $this->view->user);
    }

    public function testIsViewedTo(): void
    {
        self::assertTrue($this->view->isViewedTo($this->channel));
        self::assertFalse($this->view->isViewedTo($this->user));
    }

    public function testIsViewedBy(): void
    {
        self::assertFalse($this->view->isViewedBy($this->channel));
        self::assertTrue($this->view->isViewedBy($this->user));
    }
}
