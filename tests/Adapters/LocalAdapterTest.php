<?php

namespace Tests\Adapters;

use Mockery;
use PHPUnit\Framework\TestCase;
use LeroyMerlin\LaravelPubSub\Adapters\LocalAdapter;

class LocalAdapterTest extends TestCase
{
    public function testSubscribe()
    {
        $adapter = new LocalAdapter();

        $subscribers = $adapter->getSubscribersForChannel('test_channel');
        $this->assertIsArray($subscribers);
        $this->assertEmpty($subscribers);

        $handler = function ($message) {};

        $adapter->subscribe('test_channel', $handler);

        $subscribers = $adapter->getSubscribersForChannel('test_channel');
        $this->assertCount(1, $subscribers);
        $this->assertSame($handler, $subscribers[0]);
    }
}