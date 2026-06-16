<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class PokemonGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}
