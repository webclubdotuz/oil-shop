<?php

namespace App\Services;

use App\Models\SmsHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;


class EskizSmsService
{
    public function sendSms($phone, $message)
    {
        $url = 'notify.eskiz.uz/api/message/sms/send';

        $phone = preg_replace('/^(\+)/', '', $phone);

        $data = [
            'mobile_phone' => $phone,
            'message' => $message
        ];

        $token = env('ESKIZ_TOKEN');

        if (!$token) {
            $token = $this->getToken();
        }

        $request = Http::withToken($token)->post($url, $data);

        if ($request->status() == 401) {
            $token = $this->getToken();
            $request = Http::withToken($token)->post($url, $data);
        }

        $resposnse = $request->json();

        try {
            SmsHistory::create([
                'phone' => $phone,
                'message' => $message,
                'sms_id' => $resposnse['id'] ?? 0,
                'status' => $resposnse['status'] ?? 'error',
            ]);
        } catch (\Throwable $th) {

            SmsHistory::create([
                'phone' => $phone,
                'message' => $message,
                'sms_id' => 0,
                'status' => $th->getMessage(),
            ]);

            //throw $th;

        }



        return $request->json();

    }

    //Получить токен

    public function getToken()
    {

        $url = 'https://notify.eskiz.uz/api/auth/login';

        $data = [
            'email' => env('ESKIZ_EMAIL'),
            'password' => env('ESKIZ_PASSWORD')
        ];

        $request = Http::post($url, $data);

        if ($request->status() == 200) {

            $token = $request->json()['data']['token'];

            $this->setEnvironmentValue([
                'ESKIZ_TOKEN' => $token
            ]);

            return $request->json()['data']['token'];
        }

        return $request->json();

    }

    public function setEnvironmentValue($values = array())
    {
        if (count($values) > 0) {
            $env = File::get(base_path() . '/.env');
            $env = preg_split('/\s+/', $env);

            foreach ($values as $key => $value) {
                $key = strtoupper($key);
                $key = "ESKIZ_TOKEN";
                foreach ($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if ($entry[0] == $key) {
                        $env[$env_key] = $key . "=" . $value;
                    }
                }
            }

            $env = implode("\n", $env);
            File::put(base_path() . '/.env', $env);
        }
    }

}
