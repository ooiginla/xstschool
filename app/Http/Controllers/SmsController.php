<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Notifications\Sms\SmsService;
use App\Services\Requests\ApiRequestDto;

class SmsController extends Controller
{

    public function sendSingle(Request $request, ApiRequestDto $apiRequestDto, SmsService $smsService, )
    {
        $request->validate([
            'phonenumber' => 'required',
            'message' => 'required',
            'client_ref' => 'required'
        ]);

        $data = $request->all();

        return $smsService->singleSmsTransaction($data, $apiRequestDto);
    }
}
