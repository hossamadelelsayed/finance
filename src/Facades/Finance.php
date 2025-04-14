<?php

namespace Pickappo\Finance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pickappo\Finance\Finance
 */
class Finance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Pickappo\Finance\Finance::class;
    }
}
