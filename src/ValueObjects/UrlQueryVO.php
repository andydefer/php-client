<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;

final class UrlQueryVO extends AbstractValueObject
{
    private array $parameters = [];

    public function __construct(private string $value = '')
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

        if ($value === null) {
            $params[$key] = '';
        } else {
            $params[$key] = $value;
        }

        return new self(http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    public function withoutParameter(string $key): self
    {
        $params = $this->parameters;
        unset($params[$key]);

        return new self(http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    public function merge(array $parameters): self
    {
        return new self(http_build_query(array_merge($this->parameters, $parameters), '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * Compare deux UrlQueryVO en fonction des paramètres parsés,
     * pas de la chaîne brute (pour ignorer l'ordre).
     */
    public function equals(AbstractValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->parameters == $other->parameters;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
