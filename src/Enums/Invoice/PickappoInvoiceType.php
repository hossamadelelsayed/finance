<?php

namespace Pickappo\Finance\Enums\Invoice;

class PickappoInvoiceType
{
    const RECEIVABLE = 'receivable';
    const PAYABLE = 'payable';
    
    public static function all(): array
    {
        return [
            self::RECEIVABLE,
            self::PAYABLE
        ];
    }
}
