<?php

namespace Pickappo\Finance\DTOs\Wallets;

class GetWalletDTO
{
    public function __construct(
        public string $ownerId,
        public string $ownerType,
        public string $referenceId,
    ) {
    }
}
