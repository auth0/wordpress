<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\MessageFactory;

use Auth0\WordPress\Http\Message\Response;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface};

use function func_num_args;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (func_num_args() < 2) {
            $reasonPhrase = null;
        }

        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
}
