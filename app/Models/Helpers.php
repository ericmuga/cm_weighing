<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Helpers
{
    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }
}
