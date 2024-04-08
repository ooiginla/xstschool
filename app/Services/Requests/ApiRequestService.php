<?php

namespace App\Services\Requests;

use App\Models\Request as ApiRequest;
use Illuminate\Support\Str;
use App\Models\ServiceProvider;
use App\Models\Retry;
use App\Models\ServiceConfiguration;
use App\Services\Wallet\WalletService;
use App\Exceptions\ErrorCode;
use App\Services\Transactions\Status;
use App\Exceptions\InternalAppException;

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

        if(empty($apiRequest)) {
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
                $apiRequest->payment_status = Status::HELD;
                $apiRequest->request_status = Status::PENDING;
                $apiRequest->liened = true;

                $apiRequest->response_code = ErrorCode::CODES['REQUEST_PROCESSING']['code'];
                $apiRequest->response_message = ErrorCode::CODES['REQUEST_PROCESSING']['message'];
            }else{
                $apiRequest->payment_status =  Status::FAILED;
                $apiRequest->request_status = Status::FAILED;
                $apiRequest->liened = false;

                $apiRequest->response_code = ErrorCode::CODES['INSUFFICIENT_BALANCE']['code'];
                $apiRequest->response_message = $lienResponse->message;
            }
            
            $apiRequest->save();
        }

        if ($apiRequestDto->retry_action) 
        {
            // Check if it can work or throw exception...
            if(
                $apiRequest->request_status == Status::PENDING ||
                $apiRequest->request_status == Status::SUCCESS || 
                $apiRequest->payment_status == Status::PAID || 
                $apiRequest->payment_status == Status::HELD || 
                $apiRequest->liened == 1
            ){
                throw new InternalAppException(ErrorCode::UNABLE_TO_RETRY_STATUS_ISSUES);
            }
            
            $price = $this->getServiceCostFee($apiRequestDto->business, $apiRequestDto->service);
        
            $walletService = new WalletService();
            $lienResponse = $walletService->lienAmount($apiRequestDto->business->id, $price);


            // Store previous entries in retry table
            $retry = new Retry;
            $retry->request_id =  $apiRequest->id;
            $retry->provider_transaction_id = $apiRequest->provider_transaction_id;
            $retry->prev_request_status = $apiRequest->request_status;
            $retry->prev_payment_status = $apiRequest->payment_status;
            $retry->prev_response_code = $apiRequest->response_code;
            $retry->prev_response_message = $apiRequest->response_message;
            $retry->prev_updated_at = $apiRequest->updated_at;
            $retry->save();

            if ($lienResponse->status) {
                $apiRequest->payment_status = Status::HELD;
                $apiRequest->request_status = Status::PENDING;
                $apiRequest->liened = true;

                $apiRequest->response_code = ErrorCode::CODES['REQUEST_PROCESSING']['code'];
                $apiRequest->response_message = ErrorCode::CODES['REQUEST_PROCESSING']['message'];
            }else{
                $apiRequest->payment_status =  Status::FAILED;
                $apiRequest->request_status = Status::FAILED;
                $apiRequest->liened = false;

                $apiRequest->response_code = ErrorCode::CODES['INSUFFICIENT_BALANCE']['code'];
                $apiRequest->response_message = $lienResponse->message;
            }   
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