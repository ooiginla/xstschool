<?php

namespace App\ServiceProviders;
use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErrorCode;

class BaseServiceProvider {

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
        $provider_ref = $this->getProviderReference($response);

        $this->providerTransaction->provider_response = $response->body();
        $this->providerTransaction->provider_ref = $provider_ref;
        $this->providerTransaction->provider_httpcode = $response->status();
        $this->providerTransaction->save();
    }

    public function purchase()
    {
        $validated = $this->validateParams();

        if(! $validated) {
            return false;
        }

        $this->mapStandardToAdapterRequest();
        $this->setVerb($this->purchase_action);
        $this->setEndpoint($this->purchase_endpoint);

        return true;
    }

    public function setVerb($verb)
    {
        $this->http_verb = $verb;
    }

    public function setEndpoint($endpoint)
    {
        $this->http_endpoint = $endpoint;
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
        $validated = $this->validateStatusParams();

        if(! $validated) {
            return false;
        }

        $this->mapStandardToAdapterRequestStatus();        
        $this->setVerb($this->status_action);
        $this->setEndpoint($this->status_endpoint);

        return true;
    }

    public function processStandardPayload($standardPayload) 
    {
        $this->standardPayload = $standardPayload;

        $this->loadProviderTransaction();

        if (empty($this->providerTransaction)) {
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::CANNOT_SEE_PROVIDER_TRANSACTION, "Unable to see logged provider transaction");
            return $this->finalResponseDto;
        }
        
        $action_response = $this->{strtolower($this->providerTransaction->action)}();

        if (! $action_response) {
            return $this->finalResponseDto;
        }

        $this->saveProviderRequest();

        $response = $this->callClient($this->http_verb, $this->http_endpoint);

        $this->saveProviderResponse($response);

        $this->determineTransactionStatus($response);

        $status_updated = $this->updateProviderTransactionStatus();

        if (!  $status_updated) {
            return $this->finalResponseDto;
        }

        $this->mapAdapterResponseToStandard($response);

        $this->addProviderRawDataToFinalResponse($response);
        
        return $this->finalResponseDto;
    }

    public function addProviderRawDataToFinalResponse($response)
    {
        $this->finalResponseDto->provider_raw_data = $response->json();
    }

    public function callClient($verb, $endpoint) 
    {

        $httpReq = Http::acceptJson()->withHeaders($this->getAdditionalHeaders());

        $fullUrl = $this->getBaseUrl() . $endpoint;

        if ($verb == 'GET') {
            $response = $httpReq->get($fullUrl, $this->providerRequest);

        }elseif($verb == 'POST') {
            $response = $httpReq->post($fullUrl, $this->providerRequest);

        }elseif($verb == 'PUT') {
            $response = $httpReq->put($fullUrl, $this->providerRequest);

        }elseif($verb == 'PATCH') {
            $response = $httpReq->patch($fullUrl, $this->providerRequest);

        }elseif($verb == 'DELETE') {
            $response = $httpReq->patch($fullUrl, $this->providerRequest);

        }else{
            $response = null;

        }

        return $response;
    }
}