<?php

namespace Tests\Adapters;

use GuzzleHttp\Client;
use LeroyMerlin\LaravelPubSub\Contracts\AdapterInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use LeroyMerlin\LaravelPubSub\Adapters\HTTPAdapter;

class HTTPAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(AdapterInterface::class);
        $adapter = new HTTPAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testSetGetUri()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(AdapterInterface::class);
        $adapter = new HTTPAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertEquals('http://127.0.0.1', $adapter->getUri());
        $adapter->setUri('http://bleh');
        $this->assertEquals('http://bleh', $adapter->getUri());
    }

    public function testGetAdapter()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(AdapterInterface::class);
        $adapter = new HTTPAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertSame($subscribeAdapter, $adapter->getAdapter());
    }

    public function testSetGetUserAgent()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(AdapterInterface::class);
        $adapter = new HTTPAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertEquals('leroymerlin/php-pubsub-http', $adapter->getUserAgent());
        $adapter->setUserAgent('meh');
        $this->assertEquals('meh', $adapter->getUserAgent());
    }

    public function testGetGlobalHeaders()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(AdapterInterface::class);
        $adapter = new HTTPAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $adapter->setUserAgent('My UserAgent String');
        $headers = $adapter->getGlobalHeaders();
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertEquals('My UserAgent String', $headers['User-Agent']);
    }
}