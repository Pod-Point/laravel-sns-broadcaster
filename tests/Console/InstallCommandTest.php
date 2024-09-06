<?php

namespace PodPoint\AwsPubSub\Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        copy(config_path('app.php'), config_path('app.original'));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unlink(app_path('Providers').'/PubSubEventServiceProvider.php');
        copy(config_path('app.original'), config_path('app.php'));
    }

    #[Test]
    public function it_can_install_the_service_provider()
    {
        $this->assertFileDoesNotExist(app_path('Providers').'/PubSubEventServiceProvider.php');

        $this->artisan('pubsub:install')
            ->expectsOutput('PubSubEventServiceProvider created successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('Providers').'/PubSubEventServiceProvider.php');
    }


    #[Test]
    public function it_does_not_install_the_service_provider_if_already_existing()
    {
        $this->artisan('pubsub:install')->assertExitCode(0);

        $this->assertFileExists(app_path('Providers').'/PubSubEventServiceProvider.php');

        $this->artisan('pubsub:install')
            ->expectsOutput('PubSubEventServiceProvider already exists!')
            ->assertExitCode(1);

        $this->assertFileExists(app_path('Providers').'/PubSubEventServiceProvider.php');
    }
}
