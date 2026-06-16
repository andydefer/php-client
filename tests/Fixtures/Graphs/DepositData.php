<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Graphs;

final class DepositData
{
    public function __construct(
        public readonly string $depositId,
        public readonly string $status,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $country,
        public readonly Payer $payer,
        public readonly ?string $customerMessage,
        public readonly ?string $clientReferenceId,
        public readonly string $created,
        public readonly ?string $providerTransactionId = null,
        public readonly ?array $metadata = null,
        public readonly ?FailureReason $failureReason = null,
    ) {}

}
