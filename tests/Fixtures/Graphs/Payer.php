<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Graphs;

final class Payer
{
    public function __construct(
        public readonly string $type,
        public readonly AccountDetails $accountDetails,
    ) {}
}
