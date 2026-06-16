<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class PokemonHeight extends AbstractValueObject
{
    public function __construct(
        private readonly float $value
    ) {
        if ($value < 0) {
            throw new InvalidArgumentException('Height cannot be negative');
        }
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getInMeters(): float
    {
        return $this->value / 10;
    }

    public function getInCentimeters(): float
    {
        return $this->value * 10;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
