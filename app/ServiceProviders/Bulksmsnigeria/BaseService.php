<?php

namespace App\ServiceProviders\BulkSmsNigeria;
use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;

class BaseService {

    private $base_url = 'https://www.bulksmsnigeria.com/api/v1';
    private $api_key = 'IL6sNq0fqb0jtj6vlvcMr5pT8VZavLDCzY74p89UWdE13dJ5COJndPt86LvV';
    protected $providerTransaction= null;
    protected $providerTransactionStatus = 'PENDING';
    protected $finalResponseDto;
    protected $standardPayload;
    protected $providerRequest = [];
    protected $providerResponse = [];
    protected $additionalHeaders = [];
    protected $purchase_action = 'GET';
    protected $status_action = 'GET';

    public function setBaseUrl($url) {
        $this->base_url = $url;
    }

    public function getBaseUrl() {
        return $this->base_url;
    }

    public function setAdditionalHeaders($headers=[]) {
        $this->additionalHeaders = $headers;
    }

    public function getAdditionalHeaders() {
        return $this->additionalHeaders;
    }

    public function getApiKey() {
        return $this->api_key;
    }

    public function getProviderTransactionStatus() {
        return $this->providerTransactionStatus;
    }

    public function setProviderTransactionStatus($status) {
        if($status && in_array($status, ['PENDING','SUCCESS','FAILED'])) 
        { 
            return $this->providerTransactionStatus = $status;
        }

        return $this->providerTransactionStatus;
    }

    public function loadProviderTransaction() {
       $this->providerTransaction = ProviderTransaction::find($this->standardPayload['provider_transaction_id']);
    }

    public function updateProviderTransactionStatus()
    {
        $transaction_status = $this->getProviderTransactionStatus();

        if(in_array($transaction_status, ['PENDING','SUCCESS','FAILED'])) 
        {
            $this->providerTransaction->status = $transaction_status;
            $this->providerTransaction->save();
        }

        return true;
    }

    public function saveProviderRequest()
    {
        $this->providerTransaction->provider_request = json_encode($this->providerRequest);
        $this->providerTransaction->save();
    }

    public function saveProviderResponse($response)
    {
        $this->providerTransaction->provider_response = $response->body();
        $this->providerTransaction->save();
    }

    public function purchase()
    {
        $validated = $this->validateParams();

        if(! $validated) {
            return $this->finalResponseDto;
        }

        $this->mapStandardToAdapterRequest();
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

    public function get_status()
    {

    }

    public function processStandardPayload($standardPayload) 
    {
        $this->standardPayload = $standardPayload;

        $this->loadProviderTransaction();

        if (empty($this->providerTransaction)) {
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::CANNOT_LOG_TRANSACTION, "Unable to log provider transaction");
            return $this->finalResponseDto;
        }
        
        $this->{strtolower($this->providerTransaction->action)}();

        $this->saveProviderRequest();

        $response = $this->callClient($this->purchase_action);

        $this->saveProviderResponse($response);

        $this->determineTransactionStatus($response);

        $status_updated = $this->updateProviderTransactionStatus();

        if (!  $status_updated) {
            return $this->finalResponseDto;
        }

        $this->mapAdapterResponseToStandard($response);
        
        return $this->finalResponseDto;
    }

    public function callClient($method='POST') 
    {
        $response = null;

        $httpReq = Http::acceptJson()->withHeaders($this->getAdditionalHeaders());
        $endpoint = $this->getBaseUrl() . $this->endpoint;

        if ($method == 'GET') {
            $response = $httpReq->get($endpoint, $this->providerRequest);
        }

        if ($method == 'POST') {
            $response = $httpReq->post($endpoint, $this->providerRequest);
        }

        return $response;
    }
}