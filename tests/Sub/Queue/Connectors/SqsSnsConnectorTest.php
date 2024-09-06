<?php

namespace PodPoint\AwsPubSub\Tests\Sub\Queue\Connectors;

use PHPUnit\Framework\Attributes\Test;
use PodPoint\AwsPubSub\Sub\Queue\Connectors\SqsSnsConnector;
use PodPoint\AwsPubSub\Sub\Queue\SqsSnsQueue;
use PodPoint\AwsPubSub\Tests\TestCase;

class SqsSnsConnectorTest extends TestCase
{
    #[Test]
    public function it_can_instantiate_the_connector_and_connect_to_the_queue()
    {
        $queue = (new SqsSnsConnector)->connect([
            'driver' => 'sqs-sns',
            'key' => 'dummy-key',
            'secret' => 'dummy-secret',
            'prefix' => 'https://sqs.eu-west-1.amazonaws.com/13245',
            'queue' => 'default',
            'region' => 'eu-west-1',
        ]);

        $this->assertInstanceOf(SqsSnsQueue::class, $queue);
    }

    #[Test]
    public function it_can_use_a_queue_prefix()
    {
        $queue = (new SqsSnsConnector)->connect([
            'driver' => 'sqs-sns',
            'key' => 'dummy-key',
            'secret' => 'dummy-secret',
            'prefix' => 'https://sqs.eu-west-1.amazonaws.com/13245',
            'queue' => 'default',
            'region' => 'eu-west-1',
        ]);

        $this->assertEquals('https://sqs.eu-west-1.amazonaws.com/13245/default', $queue->getQueue(null));
    }

    #[Test]
    public function it_can_use_a_queue_suffix()
    {
        $queue = (new SqsSnsConnector)->connect([
            'driver' => 'sqs-sns',
            'key' => null,
            'secret' => null,
            'prefix' => 'https://sqs.eu-west-1.amazonaws.com/13245',
            'queue' => 'default',
            'suffix' => '-testing',
            'region' => 'eu-west-1',
        ]);

        $this->assertEquals('https://sqs.eu-west-1.amazonaws.com/13245/default-testing', $queue->getQueue(null));
    }

    #[Test]
    public function it_supports_optional_aws_credentials()
    {
        $queue = (new SqsSnsConnector)->connect([
            'driver' => 'sqs-sns',
            'prefix' => 'https://sqs.eu-west-1.amazonaws.com/13245',
            'queue' => 'default',
            'region' => 'eu-west-1',
        ]);

        $this->assertEquals('https://sqs.eu-west-1.amazonaws.com/13245/default', $queue->getQueue(null));
    }

    #[Test]
    public function it_supports_null_aws_credentials()
    {
        $queue = (new SqsSnsConnector)->connect([
            'driver' => 'sqs-sns',
            'key' => null,
            'secret' => null,
            'prefix' => 'https://sqs.eu-west-1.amazonaws.com/13245',
            'queue' => 'default',
            'region' => 'eu-west-1',
        ]);

        $this->assertEquals('https://sqs.eu-west-1.amazonaws.com/13245/default', $queue->getQueue(null));
    }
}
