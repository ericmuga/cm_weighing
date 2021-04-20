<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    public function validateLogin($post_data)
    {
        $url = config('app.login_api_url');
        $result = $this->send_curl($url, $post_data);
        return $result;
    }

    public function send_curl($url, $post_data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function addUser($username)
    {
        try {
            // try save
            DB::table('users')->insert([
                'username' => $username,
                'email' => strtolower($username) . "@farmerschoice.com",
            ]);
            return 1;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getReadScaleApiServiceUrl()
    {
        return config('app.read_scale_api_url');
    }

    public function getComportListServiceUrl()
    {
        return config('app.list_comport_api_url');
    }

    public function get_scale_read($comport)
    {
        $curl = curl_init();
        $url = $this->getReadScaleApiServiceUrl();
        $full_url = $url . '/' . $comport;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $full_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function get_comport_list()
    {
        $curl = curl_init();

        $url = $this->getComportListServiceUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
