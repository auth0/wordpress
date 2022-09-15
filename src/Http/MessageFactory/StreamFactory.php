<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\MessageFactory;

use Auth0\WordPress\Http\Message\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::create($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if ($filename === '') {
            throw new RuntimeException('Path cannot be empty');
        }

        $resource = fopen($filename, $mode);

        if ($resource === false) {
            if ($mode === '' || ! in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true)) {
                throw new InvalidArgumentException(\sprintf('The mode "%s" is invalid.', $mode));
            }

            throw new RuntimeException(sprintf('The file "%s" cannot be opened', $filename));
        }

        return Stream::create($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }
}
