<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use InvalidArgumentException;

final class PokemonStat extends AbstractValueObject
{
    public function __construct(
        private readonly string $name,
        private readonly int $base_stat
    ) {
        if ($base_stat < 0) {
            throw new InvalidArgumentException('Base stat cannot be negative');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseStat(): int
    {
        return $this->base_stat;
    }

    public function getValue(): StrictDataObject
    {
        return new StrictDataObject([
            'name' => $this->name,
            'base_stat' => $this->base_stat,
        ]);
    }

    public function __toString(): string
    {
        return sprintf('%s: %d', $this->name, $this->base_stat);
    }
}
