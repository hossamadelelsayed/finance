<?php

namespace Pickappo\Finance\DTOs\Wallets;

class CreateWalletDTO
{
    public function __construct(
        public string $ownerId,
        public string $ownerType,
        public string $referenceId,
    ) {
    }
}
