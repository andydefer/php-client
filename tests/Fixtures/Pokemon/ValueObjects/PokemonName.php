<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class PokemonName extends AbstractValueObject
{
    public function __construct(
        private readonly string $value
    ) {
        if (strlen($value) < 2) {
            throw new InvalidArgumentException('Pokemon name must be at least 2 characters');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isLegendary(): bool
    {
        return in_array(strtolower($this->value), ['mewtwo', 'rayquaza', 'arceus']);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
