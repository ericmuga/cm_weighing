<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offal extends Model
{
    protected $fillable = [
        'product_code',
        'scale_reading',
        'net_weight',
        'is_manual',
        'user_id',
        'customer_id',
        'archived',
        'updated_by',
    ];
}
