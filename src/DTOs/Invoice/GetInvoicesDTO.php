<?php

namespace Pickappo\Finance\DTOs\Invoice;

class GetInvoicesDTO
{
    public function __construct(
        public string $entityId,
        public string $entityType,
    ) {
    }
}
