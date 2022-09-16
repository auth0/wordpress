<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http;

use Auth0\WordPress\Http\MessageFactory\RequestFactory;
use Auth0\WordPress\Http\MessageFactory\ResponseFactory;
use Auth0\WordPress\Http\MessageFactory\StreamFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Factory
{
    public static function getClient(?ResponseFactoryInterface $responseFactory = null): ClientInterface
    {
        static $instance = [];

        if ($responseFactory === null) {
            $responseFactory = self::getResponseFactory();
        }

        $responseFactoryId = spl_object_hash($responseFactory);

        if (! isset($instance[$responseFactoryId])) {
            $instance[$responseFactoryId] = new Client($responseFactory);
        }

        return $instance[$responseFactoryId];
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new RequestFactory();
        }

        return $instance;
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new ResponseFactory();
        }

        return $instance;
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new StreamFactory();
        }

        return $instance;
    }
}
