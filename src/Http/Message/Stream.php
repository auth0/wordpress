<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use resource;
use RuntimeException;
use Stringable;

final class Stream implements StreamInterface, Stringable
{
    /**
     * @var array Hash of readable and writable stream types
     */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true,
            'w+' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'rb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'rt' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a+' => true,
        ],
        'write' => [
            'w' => true,
            'w+' => true,
            'rw' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'wb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a' => true,
            'a+' => true,
        ],
    ];

    /**
     * @var resource|null A resource reference
     */
    private $stream;

    private bool $seekable;

    private bool $readable;

    private bool $writable;

    private ?int $size = null;

    /**
     * @var null|mixed|bool
     */
    private $uri;

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        if ($this->seekable) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    public static function create(string|\resource|StreamInterface $body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body)) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, $body);
            $body = $resource;
        }

        if (is_resource($body)) {
            $self = new self();
            $self->stream = $body;
            $meta = stream_get_meta_data($self->stream);
            $self->seekable = $meta['seekable'] && fseek($self->stream, 0, SEEK_CUR) === 0;
            $self->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
            $self->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);

            return $self;
        }

        throw new InvalidArgumentException(
            'First argument to Stream::create() must be a string, resource or StreamInterface.'
        );
    }

    public function getContents(): string
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function close(): void
    {
        if ($this->stream !== null) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }

            $this->detach();
        }
    }

    public function detach(): ?\resource
    {
        if ($this->stream === null) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);

        $this->size = null;
        $this->uri = null;
        $this->readable = false;
        $this->seekable = false;
        $this->writable = false;
        $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if ($this->stream === null) {
            return null;
        }

        $uri = $this->getUri();

        if ($uri !== []) {
            clearstatcache(true, $uri);
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function tell(): int
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        $result = ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        if (! $this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position "' . $offset . '" with whence ' . var_export(
                $whence,
                true
            ));
        }
    }

    public function read($length): string
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        if (! $this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }

        if ($length === 0) {
            return '';
        }

        $string = fread($this->stream, $length);

        if ($string === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    public function write($string): int
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream is detached');
        }

        if (! $this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    public function getMetadata($key = null): mixed
    {
        if ($this->stream === null) {
            return $key ? null : [];
        }

        $meta = \stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    private function getUri(): array
    {
        $this->uri = $this->getMetadata('uri') ?? false;

        return $this->uri;
    }
}
