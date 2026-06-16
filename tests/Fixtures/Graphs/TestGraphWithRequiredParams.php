<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;

final class TestGraphWithRequiredParams extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}
