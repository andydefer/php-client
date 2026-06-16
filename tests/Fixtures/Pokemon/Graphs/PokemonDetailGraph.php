<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\AbilityCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\StatCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\TypeCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Enums\PokemonStatus;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonHeight;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonId;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonName;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonWeight;

final class PokemonDetailGraph extends Graph
{
    public function __construct(
        public readonly PokemonId $id,
        public readonly PokemonName $name,
        public readonly PokemonHeight $height,
        public readonly PokemonWeight $weight,
        public readonly TypeCollection $types,
        public readonly AbilityCollection $abilities,
        public readonly StatCollection $stats,
        public readonly PokemonStatus $status = PokemonStatus::ACTIVE,
        public readonly ?string $description = null,
    ) {}
}
