<?php

namespace PodPoint\AwsPubSub\Tests\Pub\BasicEvents;

use Aws\Result;
use Illuminate\Support\Facades\Log;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Tests\Pub\Concerns\InteractsWithEventBridge;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrieved;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithCustomName;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithCustomPayload;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithMultipleChannels;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\User;
use PodPoint\AwsPubSub\Tests\TestCase;

class EventBridgeTest extends TestCase
{
    use InteractsWithEventBridge;

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_with_the_event_name_as_the_detail_type_and_serialised_event_as_the_detail()
    {
        $event = new UserRetrieved($this->createJane());

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($event) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->with(m::on(function ($arg) use ($event) {
                    return $arg['Entries'][0]['Detail'] === json_encode($event)
                        && $arg['Entries'][0]['DetailType'] === UserRetrieved::class
                        && $arg['Entries'][0]['EventBusName'] === 'users'
                        && $arg['Entries'][0]['Source'] === 'my-app';
                }));
        });

        event($event);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_with_action()
    {
        $event = new UserRetrievedWithCustomName($this->createJane());

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($event) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->with(m::on(function ($arg) use ($event) {
                    return $arg['Entries'][0]['Detail'] === json_encode($event)
                        && $arg['Entries'][0]['DetailType'] === 'user.retrieved';
                }));
        });

        event($event);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_with_action_and_custom_payload()
    {
        $event = new UserRetrievedWithCustomPayload($this->createJane());

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($event) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->with(m::on(function ($arg) use ($event) {
                    $customPayload = array_merge($event->broadcastWith(), ['socket' => null]);

                    return $arg['Entries'][0]['Detail'] === json_encode($customPayload)
                        && $arg['Entries'][0]['DetailType'] === UserRetrievedWithCustomPayload::class
                        && $arg['Entries'][0]['EventBusName'] === 'users';
                }));
        });

        event($event);
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_to_multiple_channels_as_buses()
    {
        $event = new UserRetrievedWithMultipleChannels($this->createJane());

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($event) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->with(m::on(function ($arg) use ($event) {
                    return collect($event->broadcastOn())
                        ->map(function ($channel, $key) use ($arg, $event) {
                            return $arg['Entries'][$key]['Detail'] === json_encode($event)
                                && $arg['Entries'][$key]['DetailType'] === UserRetrievedWithMultipleChannels::class
                                && $arg['Entries'][$key]['EventBusName'] === $channel;
                        })
                        ->filter()
                        ->count() === 2;
                }));
        });

        event($event);
    }

    #[Test]
    /** @test */
    public function it_can_use_a_source()
    {
        config(['broadcasting.connections.eventbridge.source' => 'some-other-source']);

        $event = new UserRetrievedWithMultipleChannels($this->createJane());

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($event) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->with(m::on(function ($arg) use ($event) {
                    return collect($event->broadcastOn())
                            ->map(function ($channel, $key) use ($arg) {
                                return $arg['Entries'][$key]['Source'] === 'some-other-source';
                            })
                            ->filter()
                            ->count() > 0;
                }));
        });

        event($event);
    }

    #[Test]
    /** @test */
    public function it_logs_errors_when_events_fail_to_send()
    {
        $event = new UserRetrieved($this->createJane());

        $failedEntry = [
            'ErrorCode' => 'InternalFailure',
            'ErrorMessage' => 'Something went wrong',
            'EventId' => $this->faker->uuid,
        ];

        $this->mockEventBridge(function (MockInterface $eventBridge) use ($failedEntry) {
            $eventBridge
                ->shouldReceive('putEvents')
                ->once()
                ->andReturn(new Result([
                    'FailedEntryCount' => 1,
                    'Entries' => [
                        $failedEntry,
                        ['EventId' => $this->faker->uuid],
                    ],
                ]));
        });

        Log::shouldReceive('error')->once()->with('Failed to send events to EventBridge', [
            'errors' => [$failedEntry],
        ]);

        event($event);
    }

    protected function createJane(): User
    {
        return User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@doe.com',
            'password' => 'shh',
        ])->fresh();
    }
}
