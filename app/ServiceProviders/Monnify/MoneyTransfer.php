<?php

namespace App\ServiceProviders\Monnify;

use App\ServiceProviders\IServiceProvider;
use App\ServiceProviders\General\BaseMoneyTransfer;
use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class MoneyTransfer extends BaseMoneyTransfer implements IServiceProvider{

    use Monnify;

    protected $auth_endpoint = '/v1/auth/login';

    protected $purchase_endpoint = '/v2/disbursements/single';
    protected $purchase_action = 'POST';

    protected $status_endpoint = '/';
    protected $status_action = 'GET'; 

    public function mapStandardToAdapterRequest() 
    {
        $this->providerRequest['amount'] = $this->standardPayload['amount'];
        $this->providerRequest['reference'] = $this->getSendingReference();
        $this->providerRequest['narration'] = $this->standardPayload['destination_narration'];
        $this->providerRequest['destinationBankCode'] = $this->standardPayload['destination_code'];
        $this->providerRequest['destinationAccountNumber'] = $this->standardPayload['destination_account'];
        $this->providerRequest['currency'] = $this->standardPayload['destination_currency'];
        $this->providerRequest['sourceAccountNumber'] = $this->getUsername();
    }

    public function setAdditionalHeaders() 
    {
        $headers = array_merge($this->additionalHeaders, [
            'Authorization' => 'Bearer '.$this->getBearerToken(),
        ]); 

        $this->additionalHeaders = $headers;
    }

    public function getBearerToken()
    {
        $cachekey = "Monnify-".$this->getUsername();

        if(Cache::has($cachekey)){
            return Cache::get($cachekey);
        }

        $apikey = base64_encode($this->getApiKey());

        $response = Http::acceptJson()
                        ->withHeaders([
                            'Authorization' => "Basic ".$apikey
                        ])
                        ->post($this->getBaseUrl() . $this->auth_endpoint, []);

        $data = $response->json();

        if(!empty($data) && !empty($data['responseBody']) && !empty($data['responseBody']['accessToken'])) {

            $token = $data['responseBody']['accessToken'];
            $expires =  $data['responseBody']['expiresIn'];

            Cache::put( $cachekey, $token, $expires - 500);

            return $token;
        }
    }

    public function handleSuccessResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::SUCCESSFUL, "Transfer successfully sent");
        $this->finalResponseDto->debit_business = IServiceProvider::DEBIT_BUSINESS;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_SUCCESSFUL;
    }

    public function handlePendingResponse($response) 
    {
        $message = 'Money Transfer Pending';

        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::REQUEST_PROCESSING, $message);
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_PENDING;
    }

    public function handleFailedResponse($response) 
    {
        $data = $response->json();
        $message = $data['responseMessage'] ?? 'Money Transfer Failed';

        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, $message);
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_FAILED;
    }  
    
    public function determineTransactionStatus($response) 
    {
        $data = $response->json();

        if($response->successful())
        {
            if(isset($data['requestSuccessful']) && $data['requestSuccessful'])
            {
                if($data['responseBody']['status'] == 'SUCCESS') {
                    // Success
                    $this->setProviderTransactionStatus('SUCCESS');
                    $this->handleSuccessResponse($response);

                }else if($data['responseBody']['status'] == 'FAILED'){
                    // FAILED
                    $this->setProviderTransactionStatus('FAILED');
                    $this->handleFailedResponse($response);
                }else{
                    // do nothing
                    $this->setProviderTransactionStatus('PENDING');
                    $this->handlePendingResponse($response);
                }
            }else{
                // PENDING: BAD RESPONSE
                $this->setProviderTransactionStatus('PENDING');
                $this->handlePendingResponse($response);
            }
        }else if (
            $response->requestTimeout() ||          // 408 Request Timeout
            $response->conflict() ||                // 409 Conflict
            $response->tooManyRequests()            // 429 Too Many Requests 
        ){
            $this->handlePendingResponse($response);
        }else{
            // 500 ??
            $this->setProviderTransactionStatus('PENDING');
            $this->handlePendingResponse($response);
        }
    }

    public function getProviderReference($response)
    {
        $data = $response->json();
        $provider_reference = null;

        if(isset($data['requestSuccessful']) && $data['requestSuccessful']) {
            $provider_reference = $data['responseBody']['reference'];
        }

        return $provider_reference;
    }

    public function mockSuccessRequest()
    {
        return Http::fake([
                '*' => Http::response([
                    "requestSuccessful" => true,
                    "responseMessage" => "success",
                    "responseCode" => "0",
                    "responseBody" => [
                        "amount" => 200,
                        "reference" => "MOCK0000000001",
                        "status" => "SUCCESS",
                        "dateCreated" => "2024-05-04T12:16:23.921+0000",
                        "totalFee" => 35.00,
                        "destinationBankName" => "Zenith bank",
                        "destinationAccountNumber" => "2085886393",
                        "destinationBankCode" => "057"
                    ]
                ])], 200, ['Headers'])
                    ->acceptJson()
                    ->post('https://test.com', []);
    }

    public function  mockFailedRequest()
    {
        return  Http::fake(['*' => Http::response([
            "requestSuccessful" => true,
            "responseMessage" => "success",
            "responseCode" => "0",
            "responseBody" => [
                "amount" => 200,
                "reference" => "MOCK0000000002",
                "status" => "FAILED",
                "dateCreated" => "2024-05-04T12:16:23.921+0000",
                "totalFee" => 35.00,
                "destinationBankName" => "Zenith bank",
                "destinationAccountNumber" => "2085886393",
                "destinationBankCode" => "057"
            ]
        ])], 400, ['Headers'])
                ->acceptJson()
                ->post('https://test.com', []);
    }

    public function mockPendingRequest()
    {
        return Http::fake(['*' => Http::response([
            "requestSuccessful" => true,
            "responseMessage" => "success",
            "responseCode" => "0",
            "responseBody" => [
                "amount" => 200,
                "reference" => "MOCK0000000003",
                "status" => "PENDING_AUTHORIZATION",
                "dateCreated" => "2024-05-04T12:16:23.921+0000",
                "totalFee" => 35.00,
                "destinationBankName" => "Zenith bank",
                "destinationAccountNumber" => "2085886393",
                "destinationBankCode" => "057"
            ]
        ])], 500, ['Headers'])
            ->acceptJson()
            ->post('https://test.com', []);
    }
}