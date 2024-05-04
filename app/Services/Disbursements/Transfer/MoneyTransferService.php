<?php

namespace App\Services\Disbursements\Transfer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use App\Services\IRequestService;
use App\Services\Requests\ApiRequestDto;
use App\Services\Requests\ApiRequestService;
use App\Services\BaseService;

class MoneyTransferService extends BaseService implements IRequestService
{
    protected $narration_prefix = 'MoneyTrf';
    protected $service_name = 'MONEY_TRANSFER';
    protected $category = 'PURCHASE';
    protected $mock = true;
     

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
            
        }
    }

    public function prepareAdapterRequest()
    {
        $this->adapterRequestDto = [
            'transaction_id' => $this->transaction->oystr_ref,
            'provider' => 'default',
            'amount' => $this->requestPayload['amount'],
            'destination_narration' => $this->requestPayload['narration'],
            'destination_account' => $this->requestPayload['destination_account'],
            'destination_currency' => $this->requestPayload['destination_currency'],
            'destination_code' => $this->requestPayload['destination_code'] ?? '',
            'transfer_type' => $this->requestPayload['transfer_type'] ?? '',
            'mock_response' =>  'failed'
        ];
    }

    public function getValueNumber()
    {
        return $this->requestPayload['destination_account'] ?? '';
    }
}