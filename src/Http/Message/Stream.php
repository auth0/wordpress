<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use resource;

class Stream implements StreamInterface
{
    /** @var resource|null A resource reference */
    private $stream;

    private bool $seekable;

    private bool $readable;

    private bool $writable;

    private ?int $size;

    /** @var array|mixed|void|bool|null */
    private array $uri;

    /** @var array Hash of readable and writable stream types */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    public static function create(
        string|\resource|StreamInterface $body = ''
    ): StreamInterface {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body)) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, $body);
            $body = $resource;
        }

        if (is_resource($body)) {
            $new = new self();
            $new->stream = $body;
            $meta = stream_get_meta_data($new->stream);
            $new->seekable = $meta['seekable'] && 0 === fseek($new->stream, 0, SEEK_CUR);
            $new->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
            $new->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);

            return $new;
        }

        throw new InvalidArgumentException('First argument to Stream::create() must be a string, resource or StreamInterface.');
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
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
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }

            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        $uri = $this->getUri();

        if ($uri) {
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
        if (!isset($this->stream)) {
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
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        return feof($this->stream);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek(
        $offset,
        $whence = SEEK_SET
    ): void {
        $whence = (int) $whence;

        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position "' . $offset . '" with whence ' . var_export($whence, true));
        }
    }

    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }

        if ($length === 0) {
            return '';
        }

        $string = fread($this->stream, $length);

        if (false === $string) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->writable) {
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
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = \stream_get_meta_data($this->stream);

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    private function getUri()
    {
        if (false !== $this->uri) {
            $this->uri = $this->getMetadata('uri') ?? false;
        }

        return $this->uri;
    }
}
