<?php

namespace Pickappo\Finance\DTOs\Transactions;

use Carbon\Carbon;

class TransactionsByOwnerTypeDTO
{
    public function __construct(
        public string $ownerType,
        public string $walletType,
        public ?Carbon $startDate,
        public ?Carbon $endDate,
    ) {
    }
}
