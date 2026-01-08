<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['home', 'processLogin']]);
    }

    public function home()
    {
        if (Auth::check()) {
            // If user is logged in render the dashboard
            $title = "Navigation";
            return view('layouts.router', compact('title'));
        } else {
           // If user is not logged in render the login page
           $title = 'Login';
           return view('auth.login', compact('title'));
        }
        
    }

    public function processLogin(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        // attempt api login
        $domain_user = "FARMERSCHOICE\\" . $request->username;

        $request_data = [
            "username" => $domain_user,
            "password" => $request->password,
        ];

        $post_data = json_encode($request_data);

        $result = $helpers->validateLogin($post_data);
        $res = json_decode($result, true);

        if ($res == null) {
            # no response from api service
            Toastr::error('No response from login Api service. Contact IT', 'Error!');
            return back();
        }

        if ($res['success'] != true) {
            # failed login
            Toastr::warning('Wrong username or password. Please try again', 'Warning!');
            return back();
        }

        # login successful, check if user exists, else add
        $user = User::firstOrCreate(
            [
                'username' => $request->username
            ],
            [
                'username' => $request->username,
                'email' => strtolower($request->username) . "@farmerschoice.com",
            ]
        );

        # Check if session exists and log out the previous session
        $previous_session = $user->session;

        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
        }
        
        // Log in the user
        Auth::login($user);

        Session::put('auth_username', $request->username);

        // regenerate session to prevent session fixation
        $request->session()->regenerate();

        # Redirecting
        Toastr::success('Successful login', 'Success');
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        Toastr::success('Logout successful', 'Success');
        return redirect()->route('home');
    }
}
