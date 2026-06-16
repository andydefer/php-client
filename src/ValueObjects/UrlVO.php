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

    private readonly string $value;

    public function __construct(string $value)
    {
        // Si l'URL n'a pas de schéma, on ajoute https:// par défaut pour la validation
        $urlToValidate = $value;
        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $value)) {
            $urlToValidate = 'https://'.$value;
        }

        if (! filter_var($urlToValidate, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL: {$value}");
        }

        $parts = parse_url($urlToValidate);

        $this->scheme = $parts['scheme'] ?? 'https';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->path = $parts['path'] ?? '/';
        $this->query = new UrlQueryVO($parts['query'] ?? '');
        $this->fragment = $parts['fragment'] ?? null;
        $this->value = $value;
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
        $newValue = $this->getBaseUrl().$path;

        if (! $this->query->isEmpty()) {
            $newValue .= '?'.$this->query->toString();
        }

        if ($this->fragment !== null) {
            $newValue .= '#'.$this->fragment;
        }

        return new self($newValue);
    }

    public function withQuery(UrlQueryVO $query): self
    {
        $newValue = $this->getBaseUrl().$this->path;

        if (! $query->isEmpty()) {
            $newValue .= '?'.$query->toString();
        }

        if ($this->fragment !== null) {
            $newValue .= '#'.$this->fragment;
        }

        return new self($newValue);
    }

    public function withFragment(?string $fragment): self
    {
        $newValue = $this->getBaseUrl().$this->path;

        if (! $this->query->isEmpty()) {
            $newValue .= '?'.$this->query->toString();
        }

        if ($fragment !== null) {
            $newValue .= '#'.$fragment;
        }

        return new self($newValue);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
