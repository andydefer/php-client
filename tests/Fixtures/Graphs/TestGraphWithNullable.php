<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class TestGraphWithNullable extends Graph
{
    public function __construct(
        public readonly ?string $description = null,
    ) {}
}
