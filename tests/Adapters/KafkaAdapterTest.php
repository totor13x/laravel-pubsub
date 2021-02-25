<?php

namespace Tests\Adapters;

use LeroyMerlin\LaravelPubSub\Adapters\KafkaAdapter;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\MockKafkaErrorMessage;

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

    public function testSubscribeWithNullMessage()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $consumer->shouldReceive('subscribe')
            ->with(['channel_name'])
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($unsubscribeMessage);

        $consumer->shouldReceive('commitAsync')
            ->with($unsubscribeMessage)
            ->once();

        $adapter = new KafkaAdapter($producer, $consumer);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }

    public function testSubscribeWithPartitionEofErrorCode()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $consumer->shouldReceive('subscribe')
            ->with(['channel_name'])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;
        $message->payload = null;

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($message);

        $consumer->shouldNotReceive('commitAsnyc')
            ->with($message);

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($unsubscribeMessage);

        $consumer->shouldReceive('commitAsync')
            ->with($unsubscribeMessage)
            ->once();

        $adapter = new KafkaAdapter($producer, $consumer);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }

    public function testSubscribeWithTimedOutErrorCode()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $consumer->shouldReceive('subscribe')
            ->with(['channel_name'])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR__TIMED_OUT;
        $message->payload = null;

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($message);

        $consumer->shouldNotReceive('commitAsnyc')
            ->with($message);

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($unsubscribeMessage);

        $consumer->shouldReceive('commitAsync')
            ->with($unsubscribeMessage)
            ->once();

        $adapter = new KafkaAdapter($producer, $consumer);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }

    public function testSubscribeWithMessagePayload()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $consumer->shouldReceive('subscribe')
            ->with(['channel_name'])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $message->payload = '{"hello":"world"}';

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($message);

        $consumer->shouldReceive('commitAsync')
            ->with($message)
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';

        $consumer->shouldReceive('consume')
            ->with(120000)
            ->once()
            ->andReturn($unsubscribeMessage);

        $consumer->shouldReceive('commitAsync')
            ->with($unsubscribeMessage)
            ->once();

        $adapter = new KafkaAdapter($producer, $consumer);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();

        $adapter->subscribe('channel_name', [$handler1, 'handle']);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
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

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }

    public function testPublish()
    {
        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('produce')
            ->withArgs([
                RD_KAFKA_PARTITION_UA,
                0,
                '{"hello":"world"}',
            ])
            ->once();

        $producer = Mockery::mock(\RdKafka\Producer::class);
        $producer->shouldReceive('newTopic')
            ->with('channel_name')
            ->once()
            ->andReturn($topic);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $adapter = new KafkaAdapter($producer, $consumer);

        $adapter->publish('channel_name', ['hello' => 'world']);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }

    public function testPublishBatch()
    {
        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('produce')
            ->withArgs([
                RD_KAFKA_PARTITION_UA,
                0,
                '{"hello":"world"}',
            ])
            ->once();
        $topic->shouldReceive('produce')
            ->withArgs([
                RD_KAFKA_PARTITION_UA,
                0,
                '"lorem ipsum"',
            ])
            ->once();

        $producer = Mockery::mock(\RdKafka\Producer::class);
        $producer->shouldReceive('newTopic')
            ->with('channel_name')
            ->twice()
            ->andReturn($topic);

        $consumer = Mockery::mock(\RdKafka\KafkaConsumer::class);

        $adapter = new KafkaAdapter($producer, $consumer);

        $messages = [
            ['hello' => 'world'],
            'lorem ipsum',
        ];
        $adapter->publishBatch('channel_name', $messages);

        /**
         * Test with no required response for code coverage and prevent break purposes
         */
        $this->assertNull(null);
    }
}
