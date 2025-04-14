<?php

namespace Pickappo\Finance\Events\Wallet;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletCreatedEvent extends ShouldBeStored
{
    public function __construct(
        public string $ownerId,
        public string $ownerType,
        public string $referenceId,)
    {
    }

}
