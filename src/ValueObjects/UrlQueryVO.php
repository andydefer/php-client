<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;

final class UrlQueryVO extends AbstractValueObject
{
    private readonly array $parameters;

    public function __construct(private readonly string $value = '')
    {
        parse_str($value, $this->parameters);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function get(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function isEmpty(): bool
    {
        return empty($this->parameters);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function withParameter(string $key, mixed $value): self
    {
        $params = $this->parameters;
        $params[$key] = $value;

        return new self(http_build_query($params));
    }

    public function withoutParameter(string $key): self
    {
        $params = $this->parameters;
        unset($params[$key]);

        return new self(http_build_query($params));
    }

    public function merge(array $parameters): self
    {
        return new self(http_build_query(array_merge($this->parameters, $parameters)));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
