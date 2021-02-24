<?php

namespace Tests\Adapters;

use LeroyMerlin\LaravelPubSub\Adapters\RedisAdapter;
use Mockery;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(Client::class);
        $adapter = new RedisAdapter($client);

        $this->assertSame($client, $adapter->getClient());
    }

    public function testSubscribe()
    {
        $loop = Mockery::mock('\Tests\Mocks\MockRedisPubSubLoop[subscribe]');
        $loop->shouldReceive('subscribe')
            ->with('channel_name')
            ->once();

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('pubSubLoop')
            ->once()
            ->andReturn($loop);

        $adapter = new RedisAdapter($client);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $adapter->subscribe('channel_name', [$handler1, 'handle']);

        $this->assertNull(null);
    }

    public function testPublish()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '{"hello":"world"}',
            ])
            ->once();

        $adapter = new RedisAdapter($client);
        $adapter->publish('channel_name', ['hello' => 'world']);

        $this->assertNull(null);
    }

    public function testPublishBatch()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '"message1"',
            ])
            ->once();
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '"message2"',
            ])
            ->once();

        $adapter = new RedisAdapter($client);
        $messages = [
            'message1',
            'message2',
        ];
        $adapter->publishBatch('channel_name', $messages);

        $this->assertNull(null);
    }
}
