<?php

namespace Pickappo\Finance\Enums\Invoice;

class InvoiceEntityType
{
    const PROVIDER = 'provider';
    const LOGISTIC_COMPANY = 'company';
    
    public static function all(): array
    {
        return [
            self::PROVIDER,
            self::LOGISTIC_COMPANY
        ];
    }
}
