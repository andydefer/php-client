<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\PokemonCollection;

final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly ?string $next,
        public readonly ?string $previous,
        public readonly ?PokemonCollection $results,
    ) {}
}
