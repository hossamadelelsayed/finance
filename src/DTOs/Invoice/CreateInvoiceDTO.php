<?php

namespace Pickappo\Finance\DTOs\Invoice;

class CreateInvoiceDTO
{
    public function __construct(
        public string $entityId,
        public string $entityType,
        public $amount,
        public string $invoiceType,
        public string $status,
        public string $startDate,
        public string $endDate,
        public ?array $statistics = []
    ) {
    }
}
