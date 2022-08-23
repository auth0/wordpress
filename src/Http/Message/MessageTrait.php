<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    /** @var array<string, string[]> Map of all registered headers, as original name => array of values */
    private array $headers = [];

    /** @var array<string, string> Map of lowercase header name => original name at registration */
    private array $headerNames  = [];

    private string $protocol = '1.1';

    private StreamInterface|null $stream = null;

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion(
        string $version
    ): MessageInterface {
        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(
        string $name
    ): bool {
        return isset($this->headerNames[strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')]);
    }

    public function getHeader(
        string $name
    ): array {
        $name = strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');

        if (!isset($this->headerNames[$name])) {
            return [];
        }

        $name = $this->headerNames[$name];

        return $this->headers[$name];
    }

    public function getHeaderLine(
        string $name
    ): string {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(
        string $name,
        $value
    ): MessageInterface {
        $value = $this->sanitizeHeader($name, $value);
        $normalized = $this->normalizeHeaderKey($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    public function withAddedHeader(
        string $name,
        $value
    ): MessageInterface {
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        $new = clone $this;
        $new->setHeaders([$name => $value]);

        return $new;
    }

    public function withoutHeader(
        string $name
    ): MessageInterface {
        $normalized = $this->normalizeHeaderKey($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = Stream::create('');
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    private function setHeaders(
        array $headers
    ): void {
        foreach ($headers as $header => $value) {
            if (is_int($header)) {
                $header = (string) $header;
            }

            $value = $this->validateAndTrimHeader($header, $value);
            $normalized = $this->normalizeHeaderKey($header);

            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    private function sanitizeHeader(
        string $header,
        $values
    ): array {
        if (!is_string($header) || preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $header) === 1) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        if (!is_array($values)) {
            if ((!is_numeric($values) && !is_string($values)) || preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $values) === 1) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            return [trim((string) $values, " \t")];
        }

        if (empty($values)) {
            throw new InvalidArgumentException('Header values must be a string or an array of strings, empty array given.');
        }

        $returnValues = [];

        foreach ($values as $v) {
            if ((!is_numeric($v) && !is_string($v)) || preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $v) === 1) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            $returnValues[] = trim((string) $v, " \t");
        }

        return $returnValues;
    }

    private function normalizeHeaderKey(
        string $header
    ): string {
        return strtr($header, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }
}
