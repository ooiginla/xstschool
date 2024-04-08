<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Notifications\Sms\SmsService;
use App\Services\Requests\ApiRequestDto;

class GeneralController extends Controller
{
    public function retry(Request $request, ApiRequestDto $apiRequestDto, SmsService $service,)
    {
        $request->validate([
            'client_ref' => 'required'
        ]);

        $data = $request->all();
        $data['retry_action'] = true;

        // $service = $this->resolveService($data['client_ref']);

        return $service->process($data, $apiRequestDto);
    }

    public function resolveService()
    {
        $service = null;
        return $service;
    }
}