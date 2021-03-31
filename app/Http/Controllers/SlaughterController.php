<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;

class SlaughterController extends Controller
{
    public function index(Helpers $helpers)
    {
        $title = "dashboard";
        $date = today();

        return view('slaughter.dashboard', compact('title', 'helpers', 'date'));
    }

    public function weigh()
    {
        $title = "weigh";
        $configs = '';

        return view('slaughter.weigh', compact('title', 'configs'));
    }
}
