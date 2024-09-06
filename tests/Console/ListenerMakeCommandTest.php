<?php

namespace PodPoint\AwsPubSub\Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Tests\TestCase;

class ListenerMakeCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanup();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->cleanup();
    }

    #[Test]
    public function it_can_generate_pubsub_event_listeners()
    {
        $this->assertFileDoesNotExist(app_path('Listeners/PubSub/SomeListener.php'));

        $exitCode = $this->withoutMockingConsoleOutput()
            ->artisan('pubsub:make:listener SomeListener');

        $this->assertEquals(0, $exitCode);
        Str::contains(Artisan::output(), 'created successfully');
        $this->assertFileExists(app_path('Listeners/PubSub/SomeListener.php'));
    }

    #[Test]
    public function it_cannot_generate_pubsub_event_listeners_which_already_exist()
    {
        $this->assertFileDoesNotExist(app_path('Listeners/PubSub/SomeListener.php'));
        $this->artisan('pubsub:make:listener SomeListener')->assertSuccessful();
        $this->assertFileExists(app_path('Listeners/PubSub/SomeListener.php'));

        $exitCode = $this->withoutMockingConsoleOutput()
            ->artisan('pubsub:make:listener SomeListener');

        $this->assertEquals(0, $exitCode);
        Str::contains(Artisan::output(), 'already exists');
        $this->assertFileExists(app_path('Listeners/PubSub/SomeListener.php'));
    }

    private function cleanup()
    {
        @unlink(app_path('Listeners/PubSub/SomeListener.php'));
        @rmdir(app_path('Listeners/PubSub'));
        @rmdir(app_path('Listeners'));
    }
}
