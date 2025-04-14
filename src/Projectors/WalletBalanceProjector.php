<?php

namespace Pickappo\Finance\Projectors;

use Illuminate\Support\Facades\DB;
use Pickappo\Finance\DTOs\Wallets\CreateWalletDTO;
use Pickappo\Finance\DTOs\Wallets\GetWalletDTO;
use Pickappo\Finance\Entities\Wallet;
use Pickappo\Finance\Events\Wallet\WalletCreatedEvent;
use Pickappo\Finance\Events\Wallet\WalletCreditedEvent;
use Pickappo\Finance\Events\Wallet\WalletDebitedEvent;
use Pickappo\Finance\Events\Wallet\WalletDeletedEvent;
use Pickappo\Finance\Services\WalletService;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class WalletBalanceProjector extends Projector
{
    public function __construct(protected WalletService $walletService)
    {
    }

    protected array $handlesEvents = [
        WalletCreditedEvent::class => 'onWalletCredited',
        WalletDebitedEvent::class => 'onWalletDebited',
    ];

    public function onWalletCreated(WalletCreatedEvent $event)
    {
        $dto = new CreateWalletDTO($event->ownerId, $event->ownerType, $event->referenceId);
        $this->walletService->createWallet($dto);
    }

    public function onWalletCredited(WalletCreditedEvent $event)
    {
        DB::transaction(function () use ($event) {
            $dto = new GetWalletDTO(
                $event->ownerId,
                $event->ownerType,
                $event->walletReferenceId
            );

            $wallet = $this->walletService->getWallet(
                $dto,
                lockForUpdate:true
            );

            $wallet->balance += $event->amount;

            $wallet->save();
        });
    }

    public function onWalletDebited(WalletDebitedEvent $event)
    {
        DB::transaction(function () use ($event) {
            $dto = new GetWalletDTO(
                $event->ownerId,
                $event->ownerType,
                $event->walletReferenceId
            );

            $wallet = $this->walletService->getWallet(
                $dto,
                lockForUpdate:true
            );
            
            $wallet->balance -= $event->amount;

            $wallet->save();
        });
    }

    public function onWalletDeleted(WalletDeletedEvent $event)
    {
        Wallet::find($event->accountUuid)->delete();
    }


}
