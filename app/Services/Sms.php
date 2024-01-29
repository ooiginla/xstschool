<?php

namespace App\Services;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class Sms
{
    public function sendMessage($phone, $message) 
    {
        $response = Http::acceptJson()->get('https://www.bulksmsnigeria.com/api/v1/sms/create', [
            'from' => 'XstschAlumn',
            'to' => $phone,
            'body' => $message,
            'api_token' => 'IL6sNq0fqb0jtj6vlvcMr5pT8VZavLDCzY74p89UWdE13dJ5COJndPt86LvV',
            'append_sender' => 'XstschAlumn',
            'dnd' => 2
        ]);


        if($response->successful()){
            return true;
        }else{
            return false;
        }
    }
}