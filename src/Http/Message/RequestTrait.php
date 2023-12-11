<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;

use function is_string;

trait RequestTrait
{
    private string $method;

    private ?string $requestTarget = null;

    private \Psr\Http\Message\UriInterface $uri;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestTarget(): string
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ('' === $target) {
            $target = '/';
        }

        $query = $this->uri->getQuery();

        if ('' !== $query) {
            $target .= '?' . $query;
        }

        return $target;
    }

    public function getUri(): \Psr\Http\Message\UriInterface
    {
        return $this->uri;
    }

    /**
     * @param string $method
     */
    public function withMethod($method): static
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    public function withRequestTarget(mixed $requestTarget): static
    {
        if (! is_string($requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param bool                           $preserveHost
     */
    public function withUri(\Psr\Http\Message\UriInterface $uri, $preserveHost = false): static
    {
        if ($this->uri === $uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (! $preserveHost || ! $this->hasHeader('Host')) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();

        if ('' === $host) {
            return;
        }

        $port = $this->uri->getPort();

        if (null !== $port) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $this->headerNames['host'] = 'Host';
            $header = 'Host';
        }

        $this->headers = [
            $header => [$host],
        ] + $this->headers;
    }
}
