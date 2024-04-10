<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode;

class AdapterController extends Controller
{
    
    public function processRequest(Request $request)
    {
        if(empty($request->transaction_id)){
            return $this->returnResp(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Transaction id field is empty");
        }

        if(empty($request->provider)){
            return $this->returnResp(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Provider code field is empty");
        }

        if(empty($request->service_name)){
            return $this->returnResp(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "Service name field is empty");
        }

        $processor = $this->resolveProcessor($request->provider, $request->service_name);

        if(! $processor) {
            return $this->returnResp(false, ErrorCode::CANNOT_RESOLVE_ADAPTER, "Unable to resolve adapter class");
        }

        $data = $request->all();

        return $processor->processStandardPayload($data);
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

    public function returnResp(
            $request_status = false, 
            $response_code = '',
            $response_message = '',
            $http_code = 200,
            $data = [], 
            $provider_raw_data = [], 
            $debit_business = 'NO',
            $status = 'FAILED',
    ) 
    {

        return response()->json([
            'request_status' => $request_status,
            'response_code' => $response_code,
            'response_message' => $response_message,
            'data' => $data,
            'debit_business' => $debit_business,
            'provider_raw_data'=> $provider_raw_data,
            'status' => $status,
        ], $http_code);
    }
}