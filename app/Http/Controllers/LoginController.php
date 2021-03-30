<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class LoginController extends Controller
{
    public function login()
    {
        Toastr::success('Post added successfully :)', 'Success');
        return view('auth.login');
    }

    public function redirector()
    {
        $title = "Redirection";
        return view('layouts.router', compact('title'));
    }
}
