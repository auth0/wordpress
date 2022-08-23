<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\MessageFactory;

use Auth0\WordPress\Http\Message\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
