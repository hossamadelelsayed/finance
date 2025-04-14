<?php

namespace Pickappo\Finance\DTOs\Custody;

class GetCustodyDTO
{
    public function __construct(
        public string $companyId,
        public ?string $userId,
        public ?string $creatorId,
        public ?string $type = null,
        public ?string $fromDate = null,
        public ?string $toDate = null
    ) {
    }
}
