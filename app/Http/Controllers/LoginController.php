<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check', ['except' => ['login', 'processLogin']]);
    }

    public function login()
    {
        return view('auth.login');
    }

    public function redirector()
    {
        $title = "Redirection";
        return view('layouts.router', compact('title'));
    }
    public function processLogin(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
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
            \Session::getHandler()->destroy($previous_session);
        }

        Session::put('session_userId', $user->id);
        Session::put('session_userName', $user->username);
        Session::put('live_session_id', sha1(microtime()));

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'session' => Session::get('live_session_id'),
                'updated_at' => now(),
            ]);

        # Redirecting
        Toastr::success('Successful login', 'Success');
        return redirect()->route('redirector');
    }

    public function logout()
    {
        Session::flush();
        Toastr::success('Logout successful', 'Success');
        return redirect()->route('login');
    }
}
