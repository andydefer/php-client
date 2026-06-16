<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class TestGraphWithDefaults extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $full_name = 'default',
    ) {}
}
