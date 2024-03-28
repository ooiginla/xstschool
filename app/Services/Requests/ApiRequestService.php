<?php

namespace App\Services\Requests;

use App\Models\Request as ApiRequest;
use Illuminate\Support\Str;
use App\Models\ServiceProvider;
use App\Models\ServiceConfiguration;
use App\Services\Wallet\WalletService;
use App\Exceptions\ErrorCode;

class ApiRequestService {

    const PENDING = 'PENDING';
    const PAID = 'PAID';
    const FAILED = 'FAILED';
    const SUCCESS = 'SUCCESS';

    protected $serviceConfig = null;

   public function logRequest($apiRequestDto)
   {
        $apiRequest = ApiRequest::where('business_id', $apiRequestDto->business->id)
                                ->where('client_ref', $apiRequestDto->client_ref)
                                ->first();

        if(empty($apiRequest)) 
        {
            $price = $this->getServiceCostFee($apiRequestDto->business, $apiRequestDto->service);
        
            $walletService = new WalletService();
            $lienResponse = $walletService->lienAmount($apiRequestDto->business->id, $price);

            $apiRequest = new ApiRequest;
            $apiRequest->business_id = $apiRequestDto->business->id;
            $apiRequest->client_ref = $apiRequestDto->client_ref;
            $apiRequest->oystr_ref = $this->generateReference($apiRequestDto->service->code);
            $apiRequest->category = $apiRequestDto->service->category->name;
            $apiRequest->subcategory = $apiRequestDto->service->subcategory->name;
            $apiRequest->service_name = $apiRequestDto->service->name;
            $apiRequest->service_id = $apiRequestDto->service->id;
            $apiRequest->narration = $apiRequestDto->narration;
            $apiRequest->currency = $apiRequestDto->currency;
            $apiRequest->client_price = $price;
            $apiRequest->value_number = $apiRequestDto->value_number ?? NULL;
            $apiRequest->provider_status = NULL;

            if ($lienResponse->status) {
                $apiRequest->payment_status = self::PENDING;
                $apiRequest->request_status = self::PENDING;
                $apiRequest->liened = true;

                $apiRequest->response_code = ErrorCode::CODES['REQUEST_PROCESSING']['code'];
                $apiRequest->response_message = ErrorCode::CODES['REQUEST_PROCESSING']['message'];
            }else{
                $apiRequest->payment_status =  self::FAILED;
                $apiRequest->request_status = self::FAILED;
                $apiRequest->liened = false;

                $apiRequest->response_code = ErrorCode::CODES['INSUFFICIENT_BALANCE']['code'];
                $apiRequest->response_message = $lienResponse->message;
            }
            
            $apiRequest->save();
        }

        return $apiRequest;
   }

   public function updateSuccessfulRequest($apiRequest) 
   {
        $apiRequest->payment_status = self::PAID;
        $apiRequest->request_status = self::SUCCESS;
        $apiRequest->liened = false;

        $apiRequest->response_code = ErrorCode::CODES['SUCCESSFUL']['code'];
        $apiRequest->response_message = ErrorCode::CODES['SUCCESSFUL']['message'];
        $apiRequest->save();
   }

   public function generateReference($service_code = 'REF')
   {
       return 'OYS' . '_' . $service_code . '_' . time() . '_' . strtoupper(Str::random(5));
   }

    public function getServiceCostFee($business, $service)
    {
        $price = 0;

        $serviceConfig = ServiceConfiguration::where('business_id', $business->id)
                            ->where('service_id',  $service->id)
                            ->where('status', true)
                            ->first();

        $this->serviceConfig = $serviceConfig;


        // Custom Config Price?
        if( !empty($serviceConfig) && !is_null($serviceConfig->custom_fee)){
            $price = $serviceConfig->custom_fee;
        }else{
            // Fetch General Default Pricing
            $serviceProvider = ServiceProvider::where('service_id',  $service->id)->first();
            $price = $serviceProvider->business_fee;
        }

        return $price;
    }
}