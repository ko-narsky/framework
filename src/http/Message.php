<?php

namespace Konarsky\http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    private array $headerNames;
    private ?StreamInterface $body;

    public function __construct(
        private string $protocolVersion = '1.1',
        protected array $headers = [],
        ?StreamInterface $body = null
    ) {
        $this->headerNames = array_change_key_case(array_combine(array_map('strtolower', array_keys($this->headers)), array_keys($this->headers)));
        $this->body = $body ?: new Stream('php://temp', 'r+');
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $header = strtolower($name);

        if (isset($this->headerNames[$header]) === false) {
            return [];
        }

        return $this->headers[$this->headerNames[$header]] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        $header = strtolower($name);

        if (isset($this->headerNames[$header]) === false) {
            return '';
        }

        $headerValue = $this->headers[$this->headerNames[$header]];

        if (is_array($headerValue)) {
            return implode(', ', $headerValue);
        }

        return (string) $headerValue;
    }


    public function withHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        $normalized = strtolower($name);
        $clone->headerNames[$normalized] = $name;
        $clone->headers[$name] = (array) $value;

        return $clone;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;
        $normalized = strtolower($name);

        if (isset($clone->headerNames[$normalized])) {
            $name = $clone->headerNames[$normalized];
            $existingValues = (array) $clone->headers[$name];
            $newValues = (array) $value;
            $clone->headers[$name] = array_merge($existingValues, $newValues);
        } else {
            $clone->headerNames[$normalized] = $name;
            $clone->headers[$name] = (array) $value;
        }

        return $clone;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        $normalized = strtolower($name);

        if (isset($clone->headerNames[$normalized])) {
            $name = $clone->headerNames[$normalized];
            unset($clone->headers[$name], $clone->headerNames[$normalized]);
        }

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    private function setHeaders(): void
    {
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/',$name)) {
                $name = strtr(substr($name,5), '_', ' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name, ' ', '-');
                $this->headers[$name][] = $value;
            }
        }
    }
}
