<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'item_code',
        'unit_of_measure',
        'weight',
        'pieces',
        'location_code',
        'stock_date',
        'user_id'
    ];

    protected $table = 'stocks';
}
