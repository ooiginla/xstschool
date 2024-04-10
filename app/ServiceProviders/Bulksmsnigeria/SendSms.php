<?php

namespace App\ServiceProviders\Bulksmsnigeria;

use App\ServiceProviders\IServiceProvider;

use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;

class SendSms extends BaseService implements IServiceProvider{

    protected $endpoint = '/sms/create';
    protected $purchase_action = 'GET';
    protected $finalResponseDto;
    protected $standardPayload;
    protected $providerRequest = [];
    protected $providerResponse = [];

    public function validateParams()
    {
        $validated = false;

        if(!isset($this->standardPayload['phonenumber'])) {
            $validated = false;
            $this->finalResponseDto = new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "phonenumber field is empty");
        }

        if(!isset($this->standardPayload['message'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "message field is empty");
        }

        if(!isset($this->standardPayload['sender'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "sender field is empty");
        }
    }
    
    public function mapStandardToAdapterRequest() 
    {
        $this->providerRequest['from'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['to'] = $this->standardPayload['phonenumber'];
        $this->providerRequest['body'] = $this->standardPayload['message'];
        $this->providerRequest['api_token'] = $this->getApiKey();
        $this->providerRequest['append_sender'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['dnd'] = 2;
    }

    public function mapAdapterResponseToStandard($response) 
    {
        if($response->successful()) 
        {
            $this->handleSuccessResponse($response);
        }else if (
            $response->requestTimeout() ||          // 408 Request Timeout
            $response->conflict() ||                // 409 Conflict
            $response->tooManyRequests()         // 429 Too Many Requests 
        ){
            $this->handlePendingResponse($response);
        }else{
            $this->handleFailedResponse($response);
        }
    }

    public function getStatus() {

    }

    public function handleSuccessResponse($response) {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::SUCCESSFUL, "Sms successfully sent");
        $this->finalResponseDto->debit_business = IServiceProvider::DEBIT_BUSINESS;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_SUCCESSFUL;
    }

    public function handlePendingResponse($response) {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, "Sms status unknown");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_PENDING;
    }

    public function handleFailedResponse($response) {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, "Sms failed");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_FAILED;
    }   
}