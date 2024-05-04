<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Disbursements\Transfer\MoneyTransferService;
use App\Services\Requests\ApiRequestDto;
use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode; 

class MoneyTransferController extends Controller
{

    public function send(Request $request, ApiRequestDto $apiRequestDto, MoneyTransferService $mtService)
    {
        $request->validate([
            'amount' => 'required',
            'narration' => 'required',
            'destination_code' => 'required',
            'destination_account' => 'required',
            'destination_currency' => 'required',
            'transfer_type' => 'required',
            'client_ref' => 'required'
        ]);
        
        $data = $request->all();

        $finalResponseDto =  $mtService->process($data, $apiRequestDto);

        return $this->sendFinalResponse($finalResponseDto);
    }

    public function transform($provider, Request $request, ApiRequestDto $apiRequestDto, MoneyTransferService $smsService)
    {
        $classpath = "\\App\\Transformers\\".ucfirst($provider)."\\SendSms";

        try{
            $transformer = new $classpath();
        }catch(\Throwable $e){
            throw new InternalAppException(ErrorCode::TRANSFORMER_NOT_FOUND);
        }

        $request->merge(['transformer'=>$classpath]);

        $request = $transformer->authenticate($request);

        $standardReqest = $transformer->transformPurchaseRequest($request);

        // call Standard controller
        $response = $this->send($standardReqest, $apiRequestDto,$smsService);

        return $transformer->transformPurchaseResponse($response);
    }
}
