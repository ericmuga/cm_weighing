<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'code',
        'barcode',
        'description',
        'category',
        'unit_of_measure',
        'qty_per_unit_of_measure',
        'unit_count_per_crate',
        'blocked',
    ];
}
