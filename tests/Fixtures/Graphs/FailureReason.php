<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Graphs;

final class FailureReason
{
    public function __construct(
        public readonly string $failureCode,
        public readonly string $failureMessage,
    ) {}

}
