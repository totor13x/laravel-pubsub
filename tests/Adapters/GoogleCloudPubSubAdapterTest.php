<?php

namespace Tests\Adapters;

use Google\Cloud\PubSub\PubSubClient;
use Mockery;
use PHPUnit\Framework\TestCase;
use LaravelPubSub\Adapters\GoogleCloudPubSubAdapter;

class GoogleCloudPubSubAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testGetSetClientIdentifier()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertNull($adapter->getClientIdentifier());

        $adapter->setClientIdentifier('my_identifier');
        $this->assertEquals('my_identifier', $adapter->getClientIdentifier());
    }

    public function testGetSetAutoCreateTopics()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertTrue($adapter->areTopicsAutoCreated());

        $adapter->setAutoCreateTopics(false);
        $this->assertFalse($adapter->areTopicsAutoCreated());
    }

    public function testGetSetAutoCreateSubscriptions()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertTrue($adapter->areSubscriptionsAutoCreated());

        $adapter->setAutoCreateSubscriptions(false);
        $this->assertFalse($adapter->areSubscriptionsAutoCreated());
    }

    public function testGetSetBackgroundBatching()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertFalse($adapter->isBackgroundBatchingEnabled());

        $adapter->setBackgroundBatching(true);
        $this->assertTrue($adapter->isBackgroundBatchingEnabled());

        $adapter = new GoogleCloudPubSubAdapter($client, null, true, true, true);
        $this->assertTrue($adapter->isBackgroundBatchingEnabled());
    }

    public function testGetSetReturnImmediately()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertFalse($adapter->getReturnImmediately());

        $adapter->setReturnImmediately(true);
        $this->assertTrue($adapter->getReturnImmediately());
    }

    public function testGetSetReturnImmediatelyPause()
    {
        $client = Mockery::mock(PubSubClient::class);
        $adapter = new GoogleCloudPubSubAdapter($client);
        $this->assertEquals(500000, $adapter->getReturnImmediatelyPause());

        $adapter->setReturnImmediatelyPause(1000000);
        $this->assertEquals(1000000, $adapter->getReturnImmediatelyPause());
    }
}