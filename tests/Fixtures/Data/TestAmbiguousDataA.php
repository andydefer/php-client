<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Data;

use AndyDefer\DomainStructures\Abstracts\AbstractData;

final class TestAmbiguousDataA extends AbstractData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price = 0.0,
    ) {}
}
