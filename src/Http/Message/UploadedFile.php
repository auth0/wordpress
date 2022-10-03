<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

final class UploadedFile implements UploadedFileInterface
{
    /**
     * @var array
     */
    private const ERRORS = [
        UPLOAD_ERR_OK => 1,
        UPLOAD_ERR_INI_SIZE => 1,
        UPLOAD_ERR_FORM_SIZE => 1,
        UPLOAD_ERR_PARTIAL => 1,
        UPLOAD_ERR_NO_FILE => 1,
        UPLOAD_ERR_NO_TMP_DIR => 1,
        UPLOAD_ERR_CANT_WRITE => 1,
        UPLOAD_ERR_EXTENSION => 1,
    ];

    private int $error;

    private ?string $file = null;

    private bool $moved = false;

    private ?StreamInterface $stream = null;

    /**
     * @param StreamInterface|string $stream
     */
    public function __construct(
        string|StreamInterface $stream,
        private int $size,
        int $errorStatus,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null
    ) {
        if (! isset(self::ERRORS[$errorStatus])) {
            throw new InvalidArgumentException(
                'Upload file error status must be an integer value and one of the "UPLOAD_ERR_*" constants.'
            );
        }

        $this->error = $errorStatus;

        if ($this->error === UPLOAD_ERR_OK) {
            if (is_string($stream) && $stream !== '') {
                $this->file = $stream;
            } elseif ($stream instanceof StreamInterface) {
                $this->stream = $stream;
            } else {
                throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
            }
        }
    }

    public function getStream(): StreamInterface
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $resource = fopen($this->file, 'r');

        if ($resource === false) {
            throw new RuntimeException(sprintf('The file "%s" cannot be opened', $this->file));
        }

        return Stream::create($resource);
    }

    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if ($targetPath === '') {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        if ($this->file !== null) {
            $this->moved = PHP_SAPI === 'cli' ? rename($this->file, $targetPath) : move_uploaded_file(
                $this->file,
                $targetPath
            );

            if (! $this->moved) {
                throw new RuntimeException(sprintf('Uploaded file could not be moved to "%s"', $targetPath));
            }
        } else {
            $stream = $this->getStream();

            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $resource = fopen($targetPath, 'w');

            if ($resource === false) {
                throw new RuntimeException(sprintf('The file "%s" cannot be opened', $targetPath));
            }

            $dest = Stream::create($resource);

            while (! $stream->eof()) {
                if ($dest->write($stream->read(1_048_576)) === 0) {
                    break;
                }
            }

            $this->moved = true;
        }
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    private function validateActive(): void
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }
}
