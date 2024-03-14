<?php

namespace App\Services\Requests;

use App\Models\Request as ApiRequest;
use Illuminate\Support\Str;
use App\Models\ServiceProvider;
use App\Models\ServiceConfiguration;

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
            $apiRequest->payment_status = self::PENDING;
            $apiRequest->request_status = self::PENDING;
            $apiRequest->provider_status = NULL;
            $apiRequest->response_code = self::PENDING;
            $apiRequest->response_message = NULL;
            $apiRequest->client_price = $price;
            $apiRequest->value_number = $apiRequestDto->value_number ?? NULL;
            $apiRequest->save();
        }

        return $apiRequest;
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