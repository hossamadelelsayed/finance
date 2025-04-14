<?php

namespace Pickappo\Finance\DTOs\Custody;

class CreateCustodyDTO
{
    public function __construct(
        public string $companyId,
        public string $userId,
        public string $creatorId,
        public $amount,
        public string $type,    
        public ?string $notes,
        public ?array $attachments,
    ) {
    }
}
