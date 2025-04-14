<?php

namespace Pickappo\Finance\DTOs\Transactions;

use Pickappo\Finance\Enums\Wallet\WalletOwnerType;

class GetTransactionsDTO
{
    public function __construct(
        public string $ownerId,
        public string $referenceId,
        public string $ownerType = WalletOwnerType::CAPTAIN,
        public ?string $type = null,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?array $reasons = null
    ) {
    }
}
