<?php

namespace Pickappo\Finance\Enums\Wallet;

class WalletReferenceType
{
    const PICKAPPO = 'pickappo';
    const COMPANY = 'company';

    public static function all(): array
    {
        return [
            self::PICKAPPO, 
            self::COMPANY,
        ];
    }
}