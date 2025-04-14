<?php

namespace Pickappo\Finance\Events\Wallet;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletDeletedEvent extends ShouldBeStored
{
    public function __construct(public string $walletId)
    {
    }
}
