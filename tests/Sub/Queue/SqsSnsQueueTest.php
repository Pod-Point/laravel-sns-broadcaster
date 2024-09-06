<?php

namespace PodPoint\AwsPubSub\Tests\Sub\Queue;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Sub\Queue\Jobs\SnsEventDispatcherJob;
use PodPoint\AwsPubSub\Sub\Queue\SqsSnsQueue;
use PodPoint\AwsPubSub\Tests\Sub\Concerns\MocksNotificationMessages;
use PodPoint\AwsPubSub\Tests\TestCase;

class SqsSnsQueueTest extends TestCase
{
    use MocksNotificationMessages;

    /**
     * @var SqsClient|m\LegacyMockInterface|m\MockInterface
     */
    protected $sqs;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->sqs = m::mock(SqsClient::class);
    }

    #[Test]
    public function it_can_instantiate_the_queue()
    {
        $queue = new SqsSnsQueue($this->sqs, 'default');

        $this->assertInstanceOf(SqsSnsQueue::class, $queue);
    }

    #[Test]
    public function it_can_receive_a_rich_notification_message_and_pop_it_off_the_queue()
    {
        $this->sqs->shouldReceive('receiveMessage')
            ->once()
            ->with(['QueueUrl' => '/default', 'AttributeNames' => ['ApproximateReceiveCount']])
            ->andReturn($this->mockedRichNotificationMessage());

        $queue = new SqsSnsQueue($this->sqs, 'default');
        $queue->setContainer($this->app);
        $result = $queue->pop();

        $this->assertInstanceOf(SnsEventDispatcherJob::class, $result);
        $this->assertEquals('/default', $result->getQueue());
    }

    #[Test]
    public function it_should_use_the_queue_name_including_prefix_and_suffix()
    {
        $this->sqs->shouldReceive('receiveMessage')
            ->once()
            ->with(['QueueUrl' => 'prefix/default-suffix', 'AttributeNames' => ['ApproximateReceiveCount']])
            ->andReturn($this->mockedRichNotificationMessage());

        $queue = new SqsSnsQueue($this->sqs, 'default', 'prefix', '-suffix');
        $queue->setContainer($this->app);
        $result = $queue->pop();

        $this->assertInstanceOf(SnsEventDispatcherJob::class, $result);
        $this->assertEquals('prefix/default-suffix', $result->getQueue());
    }

    #[Test]
    public function it_properly_handles_empty_message_when_popping_it_off_the_queue()
    {
        $this->sqs->shouldReceive('receiveMessage')
            ->once()
            ->andReturn($this->mockedEmptyNotificationMessage());

        $queue = new SqsSnsQueue($this->sqs, 'default');
        $queue->setContainer($this->app);

        $this->assertNull($queue->pop());
    }

    public static function readOnlyDataProvider(): array
    {
        return [
            'pushRaw' => ['pushRaw', ['foo' => 'bar']],
            'push' => ['push', 'job'],
            'later' => ['later', 123, 'job'],
        ];
    }

    #[Test]
    #[DataProvider('readOnlyDataProvider')]
    public function it_is_a_read_only_queue_driver_and_will_not_push_messages_onto_a_queue(string $method, ...$args)
    {
        Log::shouldReceive('error')->once()->with('Unsupported: sqs-sns queue driver is read-only');
        $this->sqs->shouldNotReceive('sendMessage');

        $queue = new SqsSnsQueue($this->sqs, 'default');
        $queue->setContainer($this->app);
        $queue->$method(...$args);
    }
}
