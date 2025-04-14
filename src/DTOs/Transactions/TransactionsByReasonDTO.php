<?php

namespace Pickappo\Finance\DTOs\Transactions;

use Carbon\Carbon;

class TransactionsByReasonDTO
{
    public function __construct(
        public string $reasonType,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {
    }
}
