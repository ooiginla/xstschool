<?php

namespace App\ServiceProviders\Bulksmsnigeria;

use App\ServiceProviders\IServiceProvider;

class SendSms extends BaseService implements IServiceProvider{

    protected $endpoint = '';

    public function validateParams($data)
    {

    }
    
    public function processStandardPayload($standardPayload) 
    {
        dd($standardPayload);
        $this->validateParams($standardPayload);

        /*
        $data[
            'transaction_id' => $this->transaction->oystr_ref,
            'phonenumber' => $this->requestPayload['phonenumber'],
            'subject' => $this->requestPayload['subject'] ?? '',
            'message' => $this->requestPayload['message'] ?? '',
            'sender' => $this->requestPayload['sender'] ?? '',
            'provider' => 'default'
        ];*/
    }
   
    public function mapStandardToAdapterRequest() {

    }

    public function mapAdapterResponseToStandard() {

    }

    public function makePurchase() {

    }

    public function getStatus() {

    }

    public function callProvider() {

    }
}