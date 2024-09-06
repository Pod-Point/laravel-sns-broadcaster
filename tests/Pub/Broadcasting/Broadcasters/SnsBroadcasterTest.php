<?php

namespace PodPoint\AwsPubSub\Tests\Pub\Broadcasting\Broadcasters;

use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\EventServiceProvider;
use PodPoint\AwsPubSub\Pub\Broadcasting\Broadcasters\SnsBroadcaster;
use PodPoint\AwsPubSub\Tests\Pub\Concerns\InteractsWithSns;
use PodPoint\AwsPubSub\Tests\TestCase;

class SnsBroadcasterTest extends TestCase
{
    use InteractsWithSns;

    #[Test]
    /** @test */
    public function it_can_instantiate_the_broadcaster()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createSnsDriver([
            'driver' => 'sns',
            'key' => 'dummy-key',
            'secret' => 'dummy-secret',
            'arn-prefix' => 'aws:arn:12345:',
            'region' => 'eu-west-1',
        ]);

        $this->assertInstanceOf(SnsBroadcaster::class, $broadcaster);
    }

    #[Test]
    /** @test */
    public function it_supports_optional_aws_credentials()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createSnsDriver([
            'driver' => 'sns',
            'arn-prefix' => 'aws:arn:12345:',
            'region' => 'eu-west-1',
        ]);

        $this->assertInstanceOf(SnsBroadcaster::class, $broadcaster);
    }

    #[Test]
    /** @test */
    public function it_supports_null_aws_credentials()
    {
        $broadcaster = (new EventServiceProvider($this->app))->createSnsDriver([
            'driver' => 'sns',
            'key' => null,
            'secret' => null,
            'arn-prefix' => 'aws:arn:12345:',
            'region' => 'eu-west-1',
        ]);

        $this->assertInstanceOf(SnsBroadcaster::class, $broadcaster);
    }
}
