<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Helpers
{
    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }

    public function shortDateTime($db_time)
    {
        return date('d-m-Y H:i A', strtotime($db_time));
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

        $client_ip = \Request::getClientIp(true);

        if ($client_ip == '::1') {
            # code...
            $client_ip = 'localhost';
        }

        $url = 'http://' . $client_ip . $this->getReadScaleApiServiceUrl();

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

        $client_ip = \Request::getClientIp(true);

        if ($client_ip == '::1') {
            # code...
            $client_ip = 'localhost';
        }

        $url = 'http://' . $client_ip . $this->getComportListServiceUrl();

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

    public function insertChangeDataLogs($table_name, $item_id, $entry_type, $description)
    {
        DB::table('change_logs')->insert([
            'table_name' => $table_name,
            'item_id' => $item_id,
            'entry_type' => $entry_type,
            'description' => $description,
            'user_id' => Auth::id(),
        ]);
    }

    private $rabbitMQConnection = null;
    private $rabbitMQChannel = null;

    private function getRabbitMQConnection()
    {
        if ($this->rabbitMQConnection === null) {
            try {
                $this->rabbitMQConnection = new AMQPStreamConnection(
                    config('app.rabbitmq_host'), // RabbitMQ host
                    config('app.rabbitmq_port'), // RabbitMQ port (default for AMQP is 5672)
                    config('app.rabbitmq_user'), // RabbitMQ user
                    config('app.rabbitmq_password') // RabbitMQ password
                );
                Log::info('RabbitMQ connection established successfully.');
            } catch (\Exception $e) {
                Log::error('Failed to establish RabbitMQ connection: ' . $e->getMessage());
                throw $e;
            }
        }
        return $this->rabbitMQConnection;
    }

    private function getRabbitMQChannel()
    {
        if ($this->rabbitMQChannel === null) {
            $connection = $this->getRabbitMQConnection();
            $this->rabbitMQChannel = $connection->channel();
        }
        return $this->rabbitMQChannel;
    }


    //Rabbit MQ
    public function publishToQueue($data, $queue_name)
    {
        // Add the company name flag to the data
        $data['company_name'] = 'CM';

        $channel = $this->getRabbitMQChannel();

        try {
            $channel->exchange_declare('fcl.exchange.direct', 'direct', false, true, false);

            $msg = new AMQPMessage(json_encode($data), [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($msg, 'fcl.exchange.direct', $queue_name);
            Log::info("Message published to queue: {$queue_name}");
        } catch (\Exception $e) {
            Log::error("Failed to publish message to queue {$queue_name}: {$e->getMessage()}");
        }
    }

}
