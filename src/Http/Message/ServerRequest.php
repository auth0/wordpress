<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\{ServerRequestInterface, StreamInterface, UploadedFileInterface, UriInterface};

use function array_key_exists;
use function is_object;

/**
 * PHP stream implementation.
 */
final class ServerRequest implements ServerRequestInterface
{
    use MessageTrait;

    use RequestTrait;

    private array $attributes = [];

    private array $cookieParams = [];

    private array | object | null $parsedBody = null;

    private array $queryParams = [];

    /**
     * @var UploadedFileInterface[]
     */
    private array $uploadedFiles = [];

    /**
     * @param string                         $method       HTTP method
     * @param string|UriInterface            $uri          URI
     * @param array<string, string|string[]> $headers      Request headers
     * @param null|StreamInterface|string    $body
     * @param string                         $version      Protocol version
     * @param array                          $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(
        string $method,
        string | UriInterface $uri,
        array $headers = [],
        string | StreamInterface | null $body = null,
        string $version = '1.1',
        private array $serverParams = [],
    ) {
        if (! $uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }

        $this->method = $method;
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocol = $version;

        if (! $this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if (null === $body) {
            return;
        }

        if ('' === $body) {
            return;
        }

        $this->stream = Stream::create($body);
    }

    public function getAttribute($name, $default = null): mixed
    {
        if (! array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return mixed[]
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function getParsedBody(): array | object | null
    {
        return $this->parsedBody;
    }

    /**
     * @return mixed[]
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return mixed[]
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @return UploadedFileInterface[]
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        if (! array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }

    public function withParsedBody($data): static
    {
        if (null !== $data && [] !== $data && is_object($data)) {
            throw new InvalidArgumentException('First parameter to withParsedBody MUST be object, array or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }
}
