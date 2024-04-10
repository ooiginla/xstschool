<?php

namespace App\ServiceProviders\BulkSmsNigeria;
use App\Models\ProviderTransaction;

class BaseService {

    private $base_url = 'https://www.bulksmsnigeria.com/api/v1';
    private $api_key = 'IL6sNq0fqb0jtj6vlvcMr5pT8VZavLDCzY74p89UWdE13dJ5COJndPt86LvV';
    protected $providerTransaction= null;

    public function setBaseUrl($url) {
        $this->base_url = $url;
    }

    public function getBaseUrl() {
        return $this->base_url;
    }

    public function getApiKey() {
        return $this->api_key;
    }

    public function loadProviderTransaction() {
       $this->providerTransaction = ProviderTransaction::find($this->standardPayload['provider_transaction_id']);
    }

    public function saveProviderRequest()
    {
        $this->providerTransaction->provider_request = json_encode($this->providerRequest);
        $this->providerTransaction->save();
    }

    public function saveProviderResponse($response)
    {
        $this->providerTransaction->provider_response = $response->body;
        $this->providerTransaction->save();
    }

    public function makePurchase() 
    {
        $response = Http::acceptJson()->get($this->getBaseUrl(). $this->endpoint, $this->providerRequest);
    }

    public function processStandardPayload($standardPayload) 
    {
        $this->standardPayload = $standardPayload;

        $validated = $this->validateParams();

        if(! $validated) {
            return $this->finalResponseDto;
        }

        $logged = $this->loadProviderTransaction();

        if (! empty($logged)) {
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::CANNOT_LOG_TRANSACTION, "Unable to log provider transaction");
            return $this->finalResponseDto;
        }

        $this->mapStandardToAdapterRequest();

        $this->saveProviderRequest();

        $response = $this->makePurchase();

        $this->saveProviderResponse($response);

        $this->mapAdapterResponseToStandard($response);

        return $this->finalResponseDto;
    }
}