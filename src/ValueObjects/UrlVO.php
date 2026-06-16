<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class UrlVO extends AbstractValueObject
{
    private readonly string $scheme;

    private readonly string $host;

    private readonly ?int $port;

    private readonly string $path;

    private readonly UrlQueryVO $query;

    private readonly ?string $fragment;

    public function __construct(private readonly string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL: {$value}");
        }

        $parts = parse_url($value);

        $this->scheme = $parts['scheme'] ?? 'https';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->path = $parts['path'] ?? '/';
        $this->query = new UrlQueryVO($parts['query'] ?? '');
        $this->fragment = $parts['fragment'] ?? null;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): UrlQueryVO
    {
        return $this->query;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    public function getFullPath(): string
    {
        $path = $this->path;

        if (! $this->query->isEmpty()) {
            $path .= '?'.$this->query->toString();
        }

        if ($this->fragment !== null) {
            $path .= '#'.$this->fragment;
        }

        return $path;
    }

    public function getBaseUrl(): string
    {
        $url = $this->scheme.'://'.$this->host;

        if ($this->port !== null) {
            $url .= ':'.$this->port;
        }

        return $url;
    }

    public function withPath(string $path): self
    {
        return new self($this->getBaseUrl().$path);
    }

    public function withQuery(UrlQueryVO $query): self
    {
        $url = $this->getBaseUrl().$this->path;

        if (! $query->isEmpty()) {
            $url .= '?'.$query->toString();
        }

        return new self($url);
    }

    public function withFragment(?string $fragment): self
    {
        $url = $this->getBaseUrl().$this->path;

        if (! $this->query->isEmpty()) {
            $url .= '?'.$this->query->toString();
        }

        if ($fragment !== null) {
            $url .= '#'.$fragment;
        }

        return new self($url);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
