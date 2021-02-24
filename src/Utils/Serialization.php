<?php

namespace LeroyMerlin\LaravelPubSub\Utils;

abstract class Serialization
{
    /**
     * Serialize a message as a string.
     *
     * @param mixed $message
     *
     * @return string
     */
    public static function serializeMessage($message)
    {
        return json_encode($message);
    }

    /**
     * Unserialize the message payload.
     *
     * @param string $payload
     *
     * @return mixed
     */
    public static function unserializeMessagePayload($payload)
    {
        $message = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $message;
        }

        return $payload;
    }
}