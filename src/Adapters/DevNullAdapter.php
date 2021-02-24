<?php

namespace LeroyMerlin\LaravelPubSub\Adapters;

use LeroyMerlin\LaravelPubSub\Contracts\AdapterInterface;

/**
 * DevNull Adapter
 * @source https://github.com/Superbalist/php-pubsub/blob/master/src/Adapters/DevNullPubSubAdapter.php
 */
class DevNullAdapter implements AdapterInterface
{
    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     */
    public function subscribe($channel, callable $handler)
    {
        // you ain't subscribing to anything
    }

    /**
     * Publish a message to a channel.
     *
     * @param string $channel
     * @param mixed $message
     */
    public function publish($channel, $message)
    {
        // your message is going to /dev/null
    }

    /**
     * Publish multiple messages to a channel.
     *
     * @param string $channel
     * @param array $messages
     */
    public function publishBatch($channel, array $messages)
    {
        // your messages are going to /dev/null
    }
}