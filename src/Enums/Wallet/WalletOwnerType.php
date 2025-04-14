<?php

namespace Pickappo\Finance\Enums\Wallet;

class WalletOwnerType
{
    const LOGISTIC_COMPANY = 'company';
    const CAPTAIN = 'captain';
    const PROVIDER = 'provider';

    public static function all(): array
    {
        return [
            self::LOGISTIC_COMPANY, 
            self::CAPTAIN,
            self::PROVIDER,
        ];
    }
}
