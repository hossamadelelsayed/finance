<?php

namespace Pickappo\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Pickappo\Finance\Events\Wallet\WalletCreditedEvent;
use Pickappo\Finance\Events\Wallet\WalletDebitedEvent;

class Wallet extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function credit($amount, $referenceId, $referenceType, ?string $reason = null, ?string $subReason = null)
    {
        event(new WalletCreditedEvent(
            ownerId: $this->owner_id,
            ownerType: $this->owner_type,
            walletReferenceId: $this->reference_id,
            amount: $amount,
            referenceId: $referenceId,
            referenceType: $referenceType,
            reason: $reason,
            subReason: $subReason
        ));
    }

    public function debit($amount, $referenceId, $referenceType, ?string $reason = null, ?string $subReason = null)
    {
        event(new WalletDebitedEvent(
            ownerId: $this->owner_id,
            ownerType: $this->owner_type,
            walletReferenceId: $this->reference_id,
            amount: $amount,
            referenceId: $referenceId,
            referenceType: $referenceType,
            reason: $reason,
            subReason: $subReason
        ));
    }
}
