<?php

namespace App\Services\Notifications\Sms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use App\Services\IRequestService;
use App\Services\Requests\ApiRequestDto;
use App\Services\Requests\ApiRequestService;
use App\Services\BaseService;

class SmsService extends BaseService implements IRequestService
{
    protected $narration_prefix = 'SMS Purchase';
    protected $service_name = 'SEND_SMS';
    protected $category = 'PURCHASE';
     

    public function process($data)
    {
        return $this->processData($data);
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
        // Cherry pick Items from Adapter here: $this->adapterResponse->
        // Final Response Payload goes here
        // dd($this->adapterResponse);
        if (!empty($this->adapterResponse)) {
            $this->serviceReturnedData['name'] = $this->adapterResponse['response_code'] ?? '';
        }
    }

    public function prepareAdapterRequest()
    {
        $this->adapterRequestDto = [
            'transaction_id' => $this->transaction->oystr_ref,
            'provider' => 'default',
            'phonenumber' => $this->requestPayload['phonenumber'],
            'subject' => $this->requestPayload['subject'] ?? '',
            'message' => $this->requestPayload['message'] ?? '',
            'sender' => $this->requestPayload['sender'] ?? '',
        ];
    }

    public function getValueNumber()
    {
        return $this->requestPayload['phonenumber'] ?? '';
    }
}