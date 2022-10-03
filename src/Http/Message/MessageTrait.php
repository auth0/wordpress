<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    /**
     * @var array<string, string[]> Map of all registered headers, as original name => array of values
     */
    private array $headers = [];

    /**
     * @var array<string, string> Map of lowercase header name => original name at registration
     */
    private array $headerNames = [];

    private string $protocol = '1.1';

    private StreamInterface|null $stream = null;

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $version
     */
    public function withProtocolVersion($version): static
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return isset($this->headerNames[strtr(
            $name,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz'
        )]);
    }

    /**
     * @param string $name
     *
     * @return string[]
     *
     * @psalm-return array<string>
     */
    public function getHeader($name): array
    {
        $normalized = $this->normalizeHeaderKey($name);

        if (! isset($this->headerNames[$normalized])) {
            return [];
        }

        return $this->headers[$this->headerNames[$normalized]];
    }

    /**
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): static
    {
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

    /**
     * @param string $name
     * @param string|string[] $value
     */
    public function withAddedHeader($name, $value): static
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        $new = clone $this;
        $new->setHeaders([
            $name => $value,
        ]);

        return $new;
    }

    /**
     * @param string $name
     */
    public function withoutHeader($name): static
    {
        $this->normalizeHeaderKey($name);
        return $this;
    }

    public function getBody(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = Stream::create('');
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body): static
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    private function setHeaders(array $headers): void
    {
        foreach ($headers as $header => $value) {
            if (is_int($header)) {
                $header = (string) $header;
            }

            $value = $this->sanitizeHeader($header, $value);
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

    private function normalizeHeaderKey(string $header): string
    {
        return strtr($header, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }

    /**
      * @param string $header
      * @param string|string[] $values

     * @return string[]
     */
    private function sanitizeHeader($header, $values): array
    {
        if (preg_match("#^[!\#$%&'*+.^_`|~0-9A-Za-z-]+$#", $header) !== 1) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        if (! is_array($values)) {
            if ((! is_numeric($values) && ! is_string($values)) || preg_match(
                "@^[ \t\x21-\x7E\x80-\xFF]*$@",
                $values
            ) !== 1) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            return [trim($values, " \t")];
        }

        if (empty($values)) {
            throw new InvalidArgumentException(
                'Header values must be a string or an array of strings, empty array given.'
            );
        }

        $returnValues = [];

        foreach ($values as $v) {
            if ((! is_numeric($v) && ! is_string($v)) || preg_match(
                "@^[ \t\x21-\x7E\x80-\xFF]*$@",
                $v
            ) !== 1) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            $returnValues[] = trim($v, " \t");
        }

        return $returnValues;
    }
}
