<?php

namespace Pickappo\Finance\Enums\Order;


class OrderPaymentType
{
    public const CASH = 'CASH';
    public const CASH_PAY_AT_RESTURANT = 'CASH_PAY_AT_RESTURANT';
    public const ONLINE = 'ONLINE';
    public const ONLINE_PAY_AT_RESTURANT = 'ONLINE_PAY_AT_RESTURANT';
    
    public static function all(): array
    {
        return [
            self::CASH,
            self::CASH_PAY_AT_RESTURANT,
            self::ONLINE,
            self::ONLINE_PAY_AT_RESTURANT,
        ];
    }
}
