<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login()
    {
        Toastr::success('Post added successfully :)', 'Success');
        return view('auth.login');
    }
}
