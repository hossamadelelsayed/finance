<?php

namespace Pickappo\Finance\DTOs\Transactions;

class TransactionsResponseDTO
{
    public function __construct(
        public $transactions,
        public $totalBalance,
        public $totalOrderCost,
        public $totalOrderDeliveryCost,
        public $totalCustody,
        public $totalBonuses,
        public $totalSubractions = 0,
        public $totalTips = 0,
    ) {
    }
}
