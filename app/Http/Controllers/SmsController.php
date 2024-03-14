<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Notifications\Sms\SmsService;
use App\Services\Requests\ApiRequestDto;

class SmsController extends Controller
{

    public function sendSingle(Request $request, ApiRequestDto $apiRequestDto, SmsService $smsService, )
    {
        dd(\App\Models\ServiceProvider::select('providers.code')
                        ->join('providers', 'service_providers.provider_id', 'providers.id')
                        ->where('service_id', 1)
                        ->get());


        $request->validate([
            'phonenumber' => 'required',
            'message' => 'required',
            'client_ref' => 'required'
        ]);

        $data = $request->all();

        $smsService->singleSmsTransaction($data, $apiRequestDto);
    }
}
