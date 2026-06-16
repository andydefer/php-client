<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Graphs;

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Tests\Fixtures\Enums\TestStatus;

final class TestGraphWithEnum extends Graph
{
    public function __construct(
        public readonly TestStatus $status,
    ) {}
}
