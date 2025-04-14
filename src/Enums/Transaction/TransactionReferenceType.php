<?php

namespace Pickappo\Finance\Enums\Transaction;

class TransactionReferenceType
{
    const ORDER = 'order';
    const PICKAPPO_INVOICE = 'pickappo_invoice';
    const CUSTODY = 'custody';
    const BONUS = 'bonus';
    
    public static function all(): array
    {
        return [
            self::ORDER,
            self::PICKAPPO_INVOICE,
            self::CUSTODY,
            self::BONUS
        ];
    }
}
