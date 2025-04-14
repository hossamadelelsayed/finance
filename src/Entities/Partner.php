<?php

namespace Pickappo\Finance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;
    protected $table = 'partners';
    protected $keyType = 'string';
    public $incrementing = false;
}
