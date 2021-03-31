<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class Helpers
{
    public function authenticatedUserId()
    {
        return Session::get('session_userId');
    }

    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }

    public function forgetCache($key)
    {
        Cache::forget($key);
    }
}
