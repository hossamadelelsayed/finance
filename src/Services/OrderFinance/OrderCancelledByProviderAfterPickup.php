<?php

namespace Pickappo\Finance\Services\OrderFinance;

use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Transaction\TransactionSubReason;
use Pickappo\Finance\Events\Captain\CaptainCreditedEvent;
use Pickappo\Finance\Events\LogisticCompany\LogisticCompanyCreditedEvent;
use Pickappo\Finance\Events\Provider\ProviderDebitedEvent;

class OrderCancelledByProviderAfterPickup extends OrderFinancialBase
{
    protected function handleOnline(OrderDTOContract $order): void
    {
        $this->fireWhenCaptainPayNoThing($order);
    }

    protected function handleOnlineAndPayAtResturant(OrderDTOContract $order): void
    {
        $this->fireWhenCaptainPayAtResturant($order);
    }

    protected function handleCash(OrderDTOContract $order): void
    {
        $this->fireWhenCaptainPayNoThing($order);
    }

    protected function handleCashPayAtResturant(OrderDTOContract $order): void
    {
        $this->fireWhenCaptainPayAtResturant($order);
    }

    private function fireWhenCaptainPayAtResturant(OrderDTOContract $order)
    {
        $orderCost = $order->getOrderCost();
        $orderDeliveryCost = $order->getDeliveryCost();
        
        $this->providerWallet->debit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST
        );

        $this->providerWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );

        $this->captainWallet->credit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_REFUNDED
        );

        $this->companyWallet->credit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );
    }

    private function fireWhenCaptainPayNoThing(OrderDTOContract $order)
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
