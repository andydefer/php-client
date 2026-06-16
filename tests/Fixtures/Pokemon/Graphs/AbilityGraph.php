<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class AbilityGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}
