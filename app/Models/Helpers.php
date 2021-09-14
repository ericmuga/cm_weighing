<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::info('ports url: ' . $url);

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
        Log::info('response: ' . $response);
        return $response;
    }

    public function transformToCarcassCode($item_code)
    {
        if ($item_code == 'BG1005' || $item_code == 'BG1006') {
            // High Grade-Steer
            return 'BG1021';
        } else if ($item_code == 'BG1007') {
            // High Grade-Heifer
            return 'BG1022';
        } else if ($item_code == 'BG1008') {
            // High Grade-Bull
            return 'BG1023';
        } else if ($item_code == 'BG1009') {
            // High Grade-Cow
            return 'BG1024';
        } else if ($item_code == 'BG1011') {
            // Comm Grade - Steer
            return 'BG1031';
        } else if ($item_code == 'BG1012') {
            // Comm Grade - Heifer
            return 'BG1032';
        } else if ($item_code == 'BG1013') {
            // Comm Grade - Bull
            return 'BG1033';
        } else if ($item_code == 'BG1014') {
            // Comm Grade - Cow
            return 'BG1034';
        } else if ($item_code == 'BG1016') {
            // CMFAQ
            return 'BG1036';
        } else if ($item_code == 'BG1018') {
            // CMSTD
            return 'BG1037';
        } else if ($item_code == 'BG1101') {
            // Lamb
            return 'BG1900';
        } else if ($item_code == 'BG1201') {
            // Goat
            return 'BG1202';
        }
    }

    public function insertChangeDataLogs($table_name, $item_id, $entry_type)
    {
        DB::table('change_logs')->insert([
            'table_name' => $table_name,
            'item_id' => $item_id,
            'entry_type' => $entry_type,
            'user_id' => $this->authenticatedUserId(),
        ]);
    }
}
