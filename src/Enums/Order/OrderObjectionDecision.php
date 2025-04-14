<?php

namespace Pickappo\Finance\Enums\Order;

class OrderObjectionDecision
{
    public const FULL_FEES = 'full_fees';
    public const NO_FEES = 'no_fees';
    public const CUSTOM_FEES = 'custom_fees';
    
    public static function all(): array
    {
        return [
            self::FULL_FEES,
            self::NO_FEES,
            self::CUSTOM_FEES
        ];
    }
}
