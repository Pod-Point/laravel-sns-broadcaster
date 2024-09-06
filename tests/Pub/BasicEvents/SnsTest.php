<?php

namespace PodPoint\AwsPubSub\Tests\Pub\BasicEvents;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Tests\Pub\Concerns\InteractsWithSns;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrieved;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithCustomName;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithCustomPayload;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithMultipleChannels;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Events\UserRetrievedWithPublicProperties;
use PodPoint\AwsPubSub\Tests\Pub\TestClasses\Models\User;
use PodPoint\AwsPubSub\Tests\TestCase;

class SnsTest extends TestCase
{
    use InteractsWithSns;

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['user']['name'] === 'John Doe'
                        && $message['user']['email'] === 'john@doe.com'
                        && $message['user']['password'] === 'secret'
                        && $message['foo'] = 'bar';
                }));
        });

        event(new UserRetrieved(User::create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'secret',
        ])));
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_with_action()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['user']['email'] === 'john@doe.com'
                        && $message['action'] === 'retrieved'
                        && $message['foo'] = 'bar';
                }));
        });

        event(new UserRetrievedWithPublicProperties(User::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ])));
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_with_action_and_custom_payload()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['data']['user']['email'] === 'john@doe.com'
                        && $message['action'] === 'retrieved'
                        && $message['data']['foo'] === 'baz';
                }));
        });

        event(new UserRetrievedWithCustomPayload(User::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ])));
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_to_multiple_channels()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->twice()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['user']['email'] === 'john@doe.com'
                        && $message['foo'] = 'bat';
                }));
        });

        event(new UserRetrievedWithMultipleChannels(User::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ])));
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_name_as_subject()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['user']['email'] === 'john@doe.com'
                        && $argument['Subject'] === UserRetrieved::class;
                }));
        });

        event(new UserRetrieved(User::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ])));
    }

    #[Test]
    /** @test */
    public function it_broadcasts_basic_event_name_as_subject_if_specified()
    {
        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    $message = json_decode($argument['Message'], true);

                    return $message['user']['email'] === 'john@doe.com'
                        && $argument['Subject'] === 'user.retrieved';
                }));
        });

        event(new UserRetrievedWithCustomName(User::create([
            'name' => $this->faker->name(),
            'email' => 'john@doe.com',
            'password' => $this->faker->password(),
        ])));
    }

    #[Test]
    /** @test */
    public function it_can_use_an_arn_prefix_and_suffix()
    {
        config(['broadcasting.connections.sns.arn-prefix' => 'some-prefix:']);
        config(['broadcasting.connections.sns.arn-suffix' => '-some-suffix']);

        $this->mockSns(function (MockInterface $sns) {
            $sns->shouldReceive('publish')
                ->once()
                ->with(m::on(function ($argument) {
                    return $argument['TopicArn'] === 'some-prefix:users-some-suffix';
                }));
        });

        event(new UserRetrieved(User::create([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ])));
    }
}
