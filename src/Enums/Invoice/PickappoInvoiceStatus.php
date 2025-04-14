<?php

namespace Pickappo\Finance\Enums\Invoice;

class PickappoInvoiceStatus
{
    const PENDING = 'pending';
    const PAID = 'paid';
    const CANCELLED = 'cancelled';
    
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::CANCELLED
        ];
    }
}
