<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class PokemonId extends AbstractValueObject
{
    public function __construct(
        private readonly string $value
    ) {
        if (! preg_match('/^[a-z0-9-]+$/', $value)) {
            throw new InvalidArgumentException('Invalid Pokemon ID format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
