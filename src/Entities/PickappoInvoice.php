<?php

namespace Pickappo\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickappoInvoice extends Model
{
    use SoftDeletes;
    protected $table = 'pickappo_invoices';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $casts = [
            'statistics' => 'json'
    ];
}
