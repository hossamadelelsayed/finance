<?php

namespace Pickappo\Finance\DTOs\Transactions;

class CreateTransactionDTO
{
    public function __construct(
        public string $ownerId,
        public string $ownerType,
        public string $walletReferenceId,
        public $amount,
        public string $referenceId,
        public string $referenceType,
        public ?string $reason,
        public ?string $subReason
    ) {
    }
}
