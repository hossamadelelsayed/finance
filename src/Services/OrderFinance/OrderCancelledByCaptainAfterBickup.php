<?php

namespace Pickappo\Finance\Services\OrderFinance;

use Pickappo\Finance\Contract\OrderDTOContract;
use Pickappo\Finance\Enums\Transaction\TransactionReason;
use Pickappo\Finance\Enums\Transaction\TransactionReferenceType;
use Pickappo\Finance\Enums\Transaction\TransactionSubReason;

class OrderCancelledByCaptainAfterBickup extends OrderFinancialBase
{
    protected function handleOnline(OrderDTOContract $order): void
    {
        $orderCost = $order->getOrderCost();
        $orderDeliveryCost = $order->getDeliveryCost();

        $this->captainWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST,
            subReason: TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->captainWallet->debit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->providerWallet->credit(
            $orderCost,
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

    protected function handleOnlineAndPayAtResturant(OrderDTOContract $order): void
    {
        $orderCost = $order->getOrderCost();
        $orderDeliveryCost = $order->getDeliveryCost();

        $this->captainWallet->credit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_REFUNDED
        );
        $this->captainWallet->debit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->captainWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST,
            subReason: TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->companyWallet->credit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );
    }

    protected function handleCash(OrderDTOContract $order): void
    {
        $orderCost = $order->getOrderCost();
        $orderDeliveryCost = $order->getDeliveryCost();

        $this->captainWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST,
            subReason: TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->captainWallet->debit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->providerWallet->credit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST
        );
        $this->companyWallet->credit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );
    }

    protected function handleCashPayAtResturant(OrderDTOContract $order): void
    {
        $orderCost = $order->getOrderCost();
        $orderDeliveryCost = $order->getDeliveryCost();
        $this->captainWallet->credit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_REFUNDED
        );
        $this->captainWallet->debit(
            $orderCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_COST,
            subReason: TransactionSubReason::ORDER_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->captainWallet->debit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST,
            subReason: TransactionSubReason::ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION
        );
        $this->companyWallet->credit(
            $orderDeliveryCost,
            $order->getId(),
            TransactionReferenceType::ORDER,
            reason: TransactionReason::ORDER_DELIVERY_COST
        );
    }
}
