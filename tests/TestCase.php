<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Zing\LaravelEloquentView\Tests\Models\User;
use Zing\LaravelEloquentView\ViewServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        Schema::create(
            'users',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->timestamps();
            }
        );
        Schema::create(
            'subjects',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->timestamps();
            }
        );
    }

    protected function getEnvironmentSetUp($app): void
    {
        config(
            [
                'database.default' => 'testing',
                'eloquent-view.models.user' => User::class,
            ]
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            ViewServiceProvider::class,
        ];
    }
}
