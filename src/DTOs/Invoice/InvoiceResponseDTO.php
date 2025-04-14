<?php

namespace Pickappo\Finance\DTOs\Invoice;

class InvoiceResponseDTO
{
    public function __construct(
        public $invoice,
        public $transactions,
        public $totalPickappoFees,
        public $totalDeliveryFees,
        public $total
    ) {
    }
}
