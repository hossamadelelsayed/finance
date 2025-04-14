<?php

namespace Pickappo\Finance\Enums\Transaction;

class TransactionReason
{
    public const ORDER_DELIVERY_COST = 'order_delivery_cost';
    public const ORDER_COST = 'order_cost';
    public const ADD_CUSTODY = 'add_custody';
    public const SETTLE_CUSTODY = 'settle_custody';
    public const ADD_BONUS = 'add_bonus';
    public const PAY_INVOICE = 'pay_invoice';
    public const COLLECT_INVOICE = 'collect_invoice';
    public const PICKAPPO_COMMISSION = 'pickappo_commission';
    public const TIPS = 'tips'; 
    public const OBJECTION_ORDER_SETTLEMENT = 'objection_order_settlement';
    public const OBJECTION_DELIVERY_SETTLEMENT = 'objection_delivery_settlement';
}
