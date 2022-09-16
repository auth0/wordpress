<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

trait RequestTrait
{
    private string $method;

    private ?string $requestTarget = null;

    private ?UriInterface $uri = null;

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($target === '') {
            $target = '/';
        }

        $query = $this->uri->getQuery();

        if ($query !== null) {
            $target .= '?' . $query;
        }

        return $target;
    }

    public function withRequestTarget($requestTarget): self
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
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

        if ($host === '') {
            return;
        }

        $port = $this->uri->getPort();

        if ($port !== null) {
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
