<?php

namespace Pickappo\Finance\Events;

use Qafeer\Base\Events\QafeerEvent;
use Pickappo\Finance\Entities\PickappoInvoice;

class InvoicePaidEvent extends QafeerEvent
{
    public function __construct(public PickappoInvoice $invoice)
    {
    }
}
