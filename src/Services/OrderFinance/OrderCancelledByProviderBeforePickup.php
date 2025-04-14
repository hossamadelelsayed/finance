<?php

namespace Pickappo\Finance\Services\OrderFinance;

use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;

class OrderCancelledByProviderBeforePickup extends OrderFinancialBase
{
    protected function handleOnline(OrderDTOContract $order): void
    {
        $this->fireEvents($order);
    }

    protected function handleOnlineAndPayAtResturant(OrderDTOContract $order): void
    {
        $this->fireEvents($order);
    }

    protected function handleCash(OrderDTOContract $order): void
    {
        $this->fireEvents($order);
    }

    protected function handleCashPayAtResturant(OrderDTOContract $order): void
    {
        $this->fireEvents($order);
    }

    private function fireEvents(OrderDTOContract $order): void
    {
        $orderDeliveryCost = $order->getDeliveryCost();

        $this->providerWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );

        $this->companyWallet->credit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );
    }
}
