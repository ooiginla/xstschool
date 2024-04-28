<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Notifications\Sms\SmsService;
use App\Services\Requests\ApiRequestDto;
use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode; 

class SmsController extends Controller
{

    public function send(Request $request, ApiRequestDto $apiRequestDto, SmsService $smsService, )
    {
        $request->validate([
            'phonenumber' => 'required',
            'message' => 'required',
            'client_ref' => 'required'
        ]);

        throw new InternalAppException(ErrorCode::GOLIVE_NOT_ENABLED);
        
        $data = $request->all();

        $finalResponseDto =  $smsService->process($data, $apiRequestDto);

        return $this->sendFinalResponse($finalResponseDto);
    }

    public function transform($provider, Request $request, ApiRequestDto $apiRequestDto, SmsService $smsService)
    {
        $classpath = "\\App\\Transformers\\".ucfirst($provider)."\\SendSms";

        try{
            $transformer = new $classpath();
        }catch(\Throwable $e){
            throw new InternalAppException(ErrorCode::TRANSFORMER_NOT_FOUND);
        }

        $request = $transformer->authenticate($request);

        $standardReqest = $transformer->transformPurchaseRequest($request);

        $request->merge(['transformer'=>$classpath]);

        // call Standard controller
        $response = $this->send($standardReqest, $apiRequestDto,$smsService);

        return $transformer->transformPurchaseResponse($response);
    }
}
