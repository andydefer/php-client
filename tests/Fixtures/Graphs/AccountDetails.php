<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Graphs;

final class AccountDetails
{
    public function __construct(
        public readonly string $phoneNumber,
        public readonly string $provider,
    ) {}

}
