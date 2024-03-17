<?php

namespace App\Services\Notifications\Sms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use App\Services\Requests\ApiRequestDto;
use App\Services\Requests\ApiRequestService;
use App\Services\BaseService;

class SmsService extends BaseService
{
    protected $narration_prefix = 'SMS Purchase';
    protected $service_name = 'SEND_SMS';
    protected $category = 'PURCHASE';
    

    public function singleSmsTransaction($data)
    {
        $this->setRequestPayload($data);
        $this->mapPayloadToRequestDto();
        $this->logTransaction();
        $this->prepareAdapterRequest();
        $this->callServiceProvider();
        $this->handleProviderResponse();

       //  return $this->sendFinalResponse($this->) 
        dd('here');
    } 

    public function mapPayloadToRequestDto()
    {
        $this->apiRequestDto->client_ref = $this->requestPayload['client_ref'];
        $this->apiRequestDto->business = $this->requestPayload['business'];
        $this->apiRequestDto->service = $this->getServiceModel();
        $this->apiRequestDto->currency = $this->getCurrency();
        $this->apiRequestDto->narration = $this->getNarration();
        $this->apiRequestDto->value_number = $this->getValueNumber();
    }

    public function handleProviderResponse()
    {

    }

    public function prepareAdapterRequest()
    {
        $this->adapterRequestDto = [
            'transaction_id' => $this->transaction->oystr_ref,
            'phonenumber' => $this->requestPayload['phonenumber'],
            'subject' => $this->requestPayload['subject'] ?? '',
            'message' => $this->requestPayload['message'] ?? '',
            'sender' => $this->requestPayload['sender'] ?? '',
            'provider' => 'default'
        ];
    }

    public function getValueNumber()
    {
        return $this->requestPayload['phonenumber'] ?? '';
    }
}