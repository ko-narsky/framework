<?php

namespace Konarsky\http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    private $resource = null;
    public function __construct($stream, $mode = 'r')
    {
        if (is_resource($stream) === true) {
            $this->resource = $stream;
        }

        if (is_resource($stream) === false && is_string($stream) === true) {
            $this->resource = fopen('php://temp', 'r+');
            fwrite($this->resource, $stream);
            rewind($this->resource);
        }

        if (is_resource($stream) === false && is_string($stream) === false) {
            throw new \InvalidArgumentException('Передан недопустимый поток');
        }
    }
    public function __toString(): string
    {
        if (isset($this->resource) === false) {
            return '';
        }
        try {
            $this->rewind();
            return stream_get_contents($this->resource);
        } catch (\Exception) {
            return '';
        }
    }

    public function close(): void
    {
        if (isset($this->resource) === true) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    public function detach()
    {
        $result = $this->resource;
        $this->resource = null;
        return $result;
    }

    public function getSize(): ?int
    {
        if (isset($this->resource) === false) {
            return null;
        }

        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if (isset($this->resource) === false) {
            throw new \RuntimeException('Нет доступного ресурса');
        }

        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('Не удается определить положение указателя');
        }

        return $position;
    }

    public function eof(): bool
    {
        if (isset($this->resource) === false) {
            return false;
        }

        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        if (isset($this->resource) === false) {
            throw new \RuntimeException('Нет доступного ресурса');
        }

        return (bool)stream_get_meta_data($this->resource)['seekable'];
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $result = fseek($this->resource, $offset, $whence);
        if ($result === -1) {
            throw new \RuntimeException('Failed to seek to position in stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if (isset($this->resource) === false) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return str_contains($mode, 'x')
            || str_contains($mode, 'w')
            || str_contains($mode, 'c')
            || str_contains($mode, 'a')
            || str_contains($mode, '+');
    }

    public function write(string $string): int
    {
        if ($this->isWritable() === false) {
            throw new \RuntimeException('Ресурс недоступен для записи');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new \RuntimeException('Не удалось выполнить запись в ресурс');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        {
            if (!$this->resource) {
                return false;
            }

            $meta = stream_get_meta_data($this->resource);

            return str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');
        }
    }

    public function read(int $length): string
    {
        if ($this->isReadable() === false) {
            throw new \RuntimeException('Ресурс недоступен для чтения');
        }

        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Ну удалось выполнить чтение ресурса');
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!$this->resource) {
            throw new \RuntimeException('Нет доступного ресурса');
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new \RuntimeException('Не удается получить содержимое из ресурса');
        }

        return $contents;
    }

    public function getMetadata(?string $key = null)
    {
        if (isset($this->resource) === false) {
            return null;
        }

        $meta = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}