<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class Whatsapp
{

    public static function send_message($data)
    {




        // Kirim permintaan POST ke API WhatsApp
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('API_TOKEN')
        ])->post(env('Phone_Id'), $data);
    }
}
