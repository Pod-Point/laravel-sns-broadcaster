<?php

namespace PodPoint\AwsPubSub\Tests\Pub\Broadcasting\Broadcasters;

use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\EventServiceProvider;
use PodPoint\AwsPubSub\Pub\Broadcasting\Broadcasters\EventBridgeBroadcaster;
use PodPoint\AwsPubSub\Tests\TestCase;

class EventBridgeBroadcasterTest extends TestCase
{
    #[Test]
    public function it_can_instantiate_the_broadcaster()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createEventBridgeDriver([
            'driver' => 'eventbridge',
            'key' => 'dummy-key',
            'secret' => 'dummy-secret',
            'region' => 'eu-west-1',
            'event_bus' => 'default',
            'source' => 'my-app',
        ]);

        $this->assertInstanceOf(EventBridgeBroadcaster::class, $broadcaster);
    }

    #[Test]
    public function it_supports_optional_aws_credentials()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createEventBridgeDriver([
            'driver' => 'eventbridge',
            'region' => 'eu-west-1',
            'event_bus' => 'default',
            'source' => 'my-app',
        ]);

        $this->assertInstanceOf(EventBridgeBroadcaster::class, $broadcaster);
    }

    #[Test]
    public function it_supports_null_aws_credentials()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createEventBridgeDriver([
            'driver' => 'eventbridge',
            'key' => null,
            'secret' => null,
            'region' => 'eu-west-1',
            'event_bus' => 'default',
            'source' => 'my-app',
        ]);

        $this->assertInstanceOf(EventBridgeBroadcaster::class, $broadcaster);
    }
}
