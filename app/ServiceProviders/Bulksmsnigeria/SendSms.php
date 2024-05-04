<?php

namespace App\ServiceProviders\Bulksmsnigeria;

use App\ServiceProviders\IServiceProvider;
use App\ServiceProviders\General\BaseSendSms;


use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSms extends BaseSendSms implements IServiceProvider{

    use Bulksmsnigeria;

    protected $purchase_endpoint = '/sms/create';
    protected $purchase_action = 'GET';

    protected $status_endpoint = '/';
    protected $status_action = 'GET'; 
     
    public function mapStandardToAdapterRequest() 
    {
        $this->providerRequest['from'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['to'] = $this->standardPayload['phonenumber'];
        $this->providerRequest['body'] = $this->standardPayload['message'];
        $this->providerRequest['api_token'] = $this->getApiKey();
        $this->providerRequest['append_sender'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['dnd'] = 2;
    }

    public function handleSuccessResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::SUCCESSFUL, "Sms successfully sent");
        $this->finalResponseDto->debit_business = IServiceProvider::DEBIT_BUSINESS;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_SUCCESSFUL;
    }

    public function handlePendingResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::REQUEST_PROCESSING, "Sms status unknown");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_PENDING;
    }

    public function handleFailedResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, "Sms failed");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_FAILED;
    }  
    
    public function determineTransactionStatus($response) 
    {
        if($response->successful()){
            $this->setProviderTransactionStatus('SUCCESS');
            $this->handleSuccessResponse($response);
        }else if (
            $response->requestTimeout() ||          // 408 Request Timeout
            $response->conflict() ||                // 409 Conflict
            $response->tooManyRequests()            // 429 Too Many Requests 
        ){
            $this->handlePendingResponse($response);
        }else{
            $this->setProviderTransactionStatus('FAILED');
            $this->handleSuccessResponse($response);
        }
    }

    public function getProviderReference($response)
    {
        $data = $response->json();

        $provider_reference = (($data['data'] && $data['data']['message_id'])) ? $data['data']['message_id'] :  null;

        return $provider_reference;
    }
}