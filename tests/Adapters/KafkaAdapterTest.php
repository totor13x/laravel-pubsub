<?php

namespace Tests\Adapters;

use Mockery;
use PHPUnit\Framework\TestCase;
use LeroyMerlin\LaravelPubSub\Adapters\KafkaAdapter;
use Tests\Adapters\Mocks\MockKafkaErrorMessage;

if (!extension_loaded('rdkafka')) {
    define('RD_KAFKA_PARTITION_UA', 0);

    define('RD_KAFKA_RESP_ERR_NO_ERROR', 0);
    define('RD_KAFKA_RESP_ERR__PARTITION_EOF', 1);
    define('RD_KAFKA_RESP_ERR__TIMED_OUT', 2);
}

class KafkaAdapterTest extends TestCase
{
    public function testGetProducer()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);
        $adapter = new KafkaAdapter($producer, $consumer);
        $this->assertSame($producer, $adapter->getProducer());
    }

    public function testGetConsumer()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);
        $adapter = new KafkaAdapter($producer, $consumer);
        $this->assertSame($consumer, $adapter->getConsumer());
    }

    public function testSubscribeWithErrorThrowsException()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $consumer->shouldReceive('subscribe')
            ->with(['channel_name'])
            ->once();

        $message = new MockKafkaErrorMessage();

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($message);

        $consumer->shouldNotReceive('commitAsnyc')
            ->with($message);

        $adapter = new KafkaAdapter($producer, $consumer);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(1234);
        $this->expectExceptionMessage('This is an error message.');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }
}
