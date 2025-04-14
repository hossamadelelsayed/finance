<?php

namespace Pickappo\Finance\Enums;

class MoneyCustodyType
{
    const ADD_CUSTODY = 'add_custody';
    const SETTLE_CUSTODY = 'settle_custody';

    public static function all(): array
    {
        return [
            self::ADD_CUSTODY, 
            self::SETTLE_CUSTODY,
        ];
    }
}