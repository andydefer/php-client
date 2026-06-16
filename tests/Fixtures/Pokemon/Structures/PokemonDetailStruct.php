<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonDetailGraph;

final class PokemonDetailStruct extends Struct
{
    public function __construct(
        public readonly PokemonDetailGraph $data,
    ) {}
}
