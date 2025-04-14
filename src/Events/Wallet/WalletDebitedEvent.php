<?php

namespace Pickappo\Finance\Events\Wallet;

use Pickappo\Finance\Enums\Wallet\WalletReferenceType;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletDebitedEvent extends ShouldBeStored
{
    public function __construct(
        public string $ownerId,
        public string $ownerType,
        public $amount,
        public string $referenceId,
        public string $referenceType,
        public ?string $reason = null,
        public ?string $subReason = null,
        public string $walletReferenceId = WalletReferenceType::PICKAPPO,
    ) {
    }
}
