<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'item_code',
        'batch_no',
        'scale_reading',
        'net_weight',
        'no_of_pieces',
        'from_location_code',
        'to_location_code',
        'transfer_type',
        'narration',
        'manual_weight',
        'user_id',
        'received_weight',
        'received_pieces',
        'received_by',
        'received_date',
        'vehicle_no',
    ];
}
