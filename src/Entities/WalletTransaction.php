<?php

namespace Pickappo\Finance\Entities;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s'];

    public function reference()
    {
        return $this->morphTo();
    }
}
