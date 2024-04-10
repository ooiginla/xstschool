<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;

class AdapterController extends Controller
{

    public function processRequest(Request $request)
    {
        $this->providerTransactionId = $request->provider_transaction_id;

        if(empty($request->transaction_id)){
            return $this->returnResp(new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Transaction id field is empty"));
        }

        if(empty($request->provider)){
            return $this->returnResp(new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Provider code field is empty"));
        }

        if(empty($request->service_name)){
            return $this->returnResp(new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Service name field is empty"));
        }

        if(empty($request->provider_transaction_id)){
            return $this->returnResp(new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Unable to log provider transaction"));
        }

        $processor = $this->resolveProcessor($request->provider, $request->service_name);

        if(! $processor) {
            return $this->returnResp(new FinalResponseDto(false, ErrorCode::CANNOT_RESOLVE_ADAPTER, "Unable to resolve adapter class"));
        }

        $data = $request->all();

        return $this->returnResp($processor->processStandardPayload($data));
    } 

    public function resolveProcessor($provider, $service_name)
    {
        $classname = "\\App\\ServiceProviders\\".ucfirst($provider)."\\";
        $sname = explode("_", $service_name);
        $main_name = '';

        foreach($sname as $s){
            $main_name .= ucfirst(strtolower($s));
        }

        $classname .= $main_name;

        try{
            //dd($classname);
            $instance = new $classname();

            return $instance;
        }catch(\Throwable $e){
            return false;
        }

        return $classname;
    }

    public function returnResp($finalResponseDto) 
    {
        return response()->json([
            'request_status' => $finalResponseDto->request_status,
            'response_code' => $finalResponseDto->response_code,
            'response_message' => $finalResponseDto->response_message,
            'data' => $finalResponseDto->data,
            'debit_business' => $finalResponseDto->debit_business,
            'provider_raw_data'=> $finalResponseDto->provider_raw_data,
            'status' => $finalResponseDto->status,
        ], $finalResponseDto->http_code);
    }
}