<?php

namespace Pickappo\Finance\Enums\Transaction;

class TransactionSubReason
{
    public const ORDER_COST_REFUNDED = 'order_cost_refunded';
    public const ORDER_COST_DEDUCTED_DUE_CANCELLATION = 'order_cost_deducted_due_cancellation';
    public const ORDER_DELIVERY_COST_DEDUCTED_DUE_CANCELLATION = 'order_delivery_cost_deducted_due_cancellation';
}
