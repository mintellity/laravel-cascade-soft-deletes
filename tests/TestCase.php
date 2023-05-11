<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletesServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Mintellity\\LaravelCascadeSoftDeletes\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCascadeSoftDeletesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-cascade-soft-deletes_table.php.stub';
        $migration->up();
        */
    }
}
