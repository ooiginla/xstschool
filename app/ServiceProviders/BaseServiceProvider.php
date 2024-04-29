<?php

namespace App\ServiceProviders;
use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErrorCode;
use App\Models\BusinessIntegration;

class BaseServiceProvider {

    protected $credentials = null;
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

    public function setAdditionalHeaders() {
        $this->additionalHeaders = [];
    }

    public function getAdditionalHeaders() {
        return $this->additionalHeaders;
    }

    public function getProvider(){
        return  Provider::where('code', $this->provider_code)->first();
     }
 
     public function getBusiness(){
         return Businss::where('username',$this->standardPayload['sa_business'])->first();
     }
 
     public function getApiKey() {
         return $this->credentials->public_api_key;
     }
 
    public function getPrivateKey() {
       return $this->credentials->private_api_key;
    }

    public function getUsername() {
        return $this->credentials->username;
     }
 
    public function getPassword() {
       return $this->credentials->password;
    }
 
     public function getCallbackUrl() {
       return $this->credentials->callback_url;
     }

    public function getProviderTransactionStatus() {
        return $this->providerTransactionStatus;
    }

    public function setProviderTransactionStatus($status) 
    {
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
        // 
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

    public function setCredentials(){
        $this->credentials = BusinessIntegration::where('id', $this->standardPayload['integration_id'])->first();
    }

    public function init()
    {

    }

    public function processStandardPayload($standardPayload) 
    {
        $this->standardPayload = $standardPayload;

        $this->loadProviderTransaction();
        $this->init();
        $this->setCredentials();

        if (empty($this->providerTransaction)) {
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::CANNOT_SEE_PROVIDER_TRANSACTION, "Unable to see logged provider transaction");
            return $this->finalResponseDto;
        }
        
        $action_response = $this->{strtolower($this->providerTransaction->action)}();

        if (! $action_response) {
            return $this->finalResponseDto;
        }

        $this->setAdditionalHeaders();

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

        if(isset($this->standardPayload['mock_response'])) 
        {
            $return_value = match ($this->standardPayload['mock_response']) {
                'success' => $this->mockSuccessRequest(),
                'failed' =>  $this->mockFailedRequest(),
                'pending' => $this->mockPendingRequest(),
            };

            return $return_value;
        }

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

       // dd($response->clientError());

        return $response;
    }
}