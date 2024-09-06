<?php

namespace PodPoint\AwsPubSub\Tests\Pub;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Tests\Pub\Concerns\InteractsWithSns;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\User;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEvents;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEventsWhenUpdatedOnly;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEventsWithCustomName;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEventsWithCustomPayload;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEventsWithCustomPayloadWhenUpdatedOnly;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\UserWithBroadcastingEventsWithMultipleChannels;
use PodPoint\AwsPubSub\Tests\TestCase;

class ModelEventsTest extends TestCase
{
    use InteractsWithSns;

    #[Test]
    /** @test */
    public function it_broadcasts_model_event()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['model']['email'] === 'john@doe.com';
                }));
        });

        UserWithBroadcastingEvents::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ]);
    }

    #[Test]
    /** @test */
    public function it_does_not_broadcast_model_events_without_trait()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldNotHaveReceived('publish');
        });

        User::create([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ]);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_event_with_custom_payload()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['data']['user']['email'] === 'john@doe.com'
                        && $message['action'] === 'created'
                        && $message['data']['foo'] === 'bar';
                }));
        });

        UserWithBroadcastingEventsWithCustomPayload::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ]);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_event_with_specified_event()
    {
        $user = UserWithBroadcastingEventsWhenUpdatedOnly::create([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ]);

        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['model']['email'] === 'john@doe.com';
                }));
        });

        $user->update([
            'email' => 'john@doe.com',
        ]);
    }

    #[Test]
    /** @test */
    public function it_does_not_broadcast_model_event_without_specified_event()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldNotHaveReceived('publish');
        });

        UserWithBroadcastingEventsWhenUpdatedOnly::create([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ])->delete();
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_event_with_specified_event_and_custom_payload()
    {
        $user = UserWithBroadcastingEventsWithCustomPayloadWhenUpdatedOnly::create([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ]);

        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['data']['user']['email'] === 'john@doe.com'
                        && $message['action'] === 'updated'
                        && $message['data']['foo'] === 'baz';
                }));
        });

        $user->update([
            'email' => 'john@doe.com',
        ]);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_events_to_multiple_channels()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->twice()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['model']['email'] === 'john@doe.com';
                }));
        });

        UserWithBroadcastingEventsWithMultipleChannels::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ]);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_event_name_as_subject()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    return $argument['Subject'] === 'UserWithBroadcastingEventsCreated';
                }));
        });

        UserWithBroadcastingEvents::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ]);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_model_event_name_as_subject_if_specified()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    return $argument['Subject'] === 'user.created';
                }));
        });

        UserWithBroadcastingEventsWithCustomName::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ]);
    }
}
