<?php

namespace App\ServiceProviders\Africastalking;

use App\ServiceProviders\IServiceProvider;
use App\ServiceProviders\General\BaseSendSms;


use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\ServiceProviders\Africastalking\Africastalking;

class SendSms extends BaseSendSms implements IServiceProvider 
{

    use Africastalking;
 
    protected $purchase_endpoint = '/messaging';
    protected $purchase_action = 'POST';

    protected $status_endpoint = '/';
    protected $status_action = 'GET'; 

    public function init()
    {
        $this->setCredentials();
    }
     
    public function mapStandardToAdapterRequest() 
    {
        // $this->providerRequest['from'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['username'] =  $this->getUsername();
        $this->providerRequest['message'] = $this->standardPayload['message'];
        $this->providerRequest['to'] = $this->standardPayload['phonenumber'];
    }

    public function setAdditionalHeaders() 
    {
        $headers = array_merge($this->additionalHeaders, [
            'apiKey' => $this->getApiKey(),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]); 

        $this->additionalHeaders = $headers;
    }

    public function handleSuccessResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::SUCCESSFUL, "Sms successfully sent");
        $this->finalResponseDto->debit_business = IServiceProvider::DEBIT_BUSINESS;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_SUCCESSFUL;
    }

    public function handlePendingResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::REQUEST_PROCESSING, "Sms status unknown");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_PENDING;
    }

    public function handleFailedResponse($response) 
    {
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, "Sms failed");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_FAILED;
    }  
    
    public function determineTransactionStatus($response) 
    {
        if($response->successful())
        {
            $data = $response->json();

            if(isset($data['SMSMessageData']) && isset($data['SMSMessageData']['Recipients']) &&  $data['SMSMessageData']['Recipients'] > 0 && 
                        in_array($data['SMSMessageData']['Recipients'][0]['statusCode'], ["101","102","103"])) 
            {
                $this->setProviderTransactionStatus('SUCCESS');
                $this->handleSuccessResponse($response);
            }else{
                $this->setProviderTransactionStatus('FAILED');
                $this->handleFailedResponse($response);
            }
        }else if (
            $response->requestTimeout() ||          // 408 Request Timeout
            $response->conflict() ||                // 409 Conflict
            $response->tooManyRequests()            // 429 Too Many Requests 
        ){
            $this->handlePendingResponse($response);
        }else{
            // 500 ??
            $this->setProviderTransactionStatus('FAILED');
            $this->handleFailedResponse($response);
        }
    }

    public function getProviderReference($response)
    {
        $provider_reference = null;

        $data = $response->json();

        if (isset($data['SMSMessageData']) && isset($data['SMSMessageData']['Recipients']) && $data['SMSMessageData']['Recipients'] > 0) {
           return $data['SMSMessageData']['Recipients'][0]['messageId'];
        }

        return $provider_reference;
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

        $httpReq = Http::acceptJson()
                        ->asForm()
                        ->withHeaders($this->getAdditionalHeaders())
                         ->withBody(http_build_query($this->providerRequest), 'application/x-www-form-urlencoded');

        $fullUrl = $this->getBaseUrl() . $endpoint;

        $this->providerRequest = [$this->providerRequest];

        $response = $httpReq->post($fullUrl);

        return $response;
    }

    public function mockSuccessRequest()
    {
        return Http::fake([
                '*' => Http::response([
                    'status' => 'SUCCESS',
                    'response_code' => 'SUCCESSFUL',
                    'response_message' => 'Sms Sent',
                    'debit_business' => 'YES',
                    'data'=>[],
                    'provider_raw_data' => [
                        "SMSMessageData" => [
                            "Message" => "Mocked Sent to 1/1 Total Cost: NGN 2.2000",
                            "Recipients" => [
                                [
                                    "cost" => "NGN 2.2000",
                                    "messageId" => "ATXid_a8699a2fdee954d2b4554a3034af9185",
                                    "messageParts" => 1,
                                    "number" => "+2347035361770",
                                    "status" => "Success",
                                    "statusCode" => 101
                                ]
                            ]
                        ]
                    ]
                ])
            ], 200, ['Headers'])
                    ->acceptJson()
                    ->post('https://google.com', []);
    }

    public function  mockFailedRequest()
    {
        return  Http::fake(['*' => Http::response([
                    'status' => 'FAILED',
                    'response_code' => 'TRANSACTION_NOT_FOUND',
                    'response_message' => 'Sms not sent',
                    'debit_business' => 'NO',
                    'data'=> [],
                    'provider_raw_data' => [
                        "SMSMessageData" => [
                            "Message" => "mocked Sent to 0/1 Total Cost: 0",
                            "Recipients" => [
                                [
                                    "cost" => "0",
                                    "messageId" => "None",
                                    "number" => "07035361770",
                                    "status" => "InvalidPhoneNumber",
                                    "statusCode" => 403
                                ]
                            ]
                        ]
                    ]
                ])
            ], 400, ['Headers'])
                ->acceptJson()
                ->post('https://google.com', []);
    }

    public function mockPendingRequest()
    {
        return Http::fake($success, 200, ['Headers'])
                    ->acceptJson()
                    ->post($endpoint, []);
    }
}