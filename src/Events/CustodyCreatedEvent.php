<?php

namespace Pickappo\Finance\Events;

use Qafeer\Base\Events\QafeerEvent;
use Pickappo\Finance\Entities\Custody;

class CustodyCreatedEvent extends QafeerEvent
{
    public function __construct(public Custody $custody) {
    }
}
