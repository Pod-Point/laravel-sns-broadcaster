<?php

namespace PodPoint\AwsPubSub\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as Orchestra;
use PodPoint\AwsPubSub\AwsPubSubServiceProvider;
use PodPoint\AwsPubSub\EventServiceProvider;

abstract class TestCase extends Orchestra
{
    use WithFaker;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            AwsPubSubServiceProvider::class,
            EventServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $this->configureTestDatabase($app);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function configureTestDatabase($app)
    {
        $app->config->set('database.default', 'testbench');
        $app->config->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
