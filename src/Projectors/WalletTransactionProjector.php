<?php

namespace Pickappo\Finance\Projectors;

use Pickappo\Finance\DTOs\Transactions\CreateTransactionDTO;
use Pickappo\Finance\Events\Wallet\WalletCreditedEvent;
use Pickappo\Finance\Events\Wallet\WalletDebitedEvent;
use Pickappo\Finance\Services\TransactionService;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class WalletTransactionProjector extends Projector
{
    public function __construct(protected TransactionService $transactionService)
    {
    }

    protected array $handlesEvents = [
        WalletCreditedEvent::class => 'onWalletCredited',
        WalletDebitedEvent::class => 'onWalletDebited',
    ];

    public function onWalletCredited(WalletCreditedEvent $event)
    {
        $dto = new CreateTransactionDTO(
            $event->ownerId,
            $event->ownerType,
            $event->walletReferenceId,
            $event->amount,
            $event->referenceId,
            $event->referenceType,
            $event->reason,
            $event->subReason
        );
        $this->transactionService->create($dto);
    }

    public function onWalletDebited(WalletDebitedEvent $event)
    {
        $dto = new CreateTransactionDTO(
            $event->ownerId,
            $event->ownerType,
            $event->walletReferenceId,
            $event->amount * -1,
            $event->referenceId,
            $event->referenceType,
            $event->reason,
            $event->subReason
        );
        $this->transactionService->create($dto);
    }
}
