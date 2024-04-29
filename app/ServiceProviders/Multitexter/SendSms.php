<?php

namespace App\ServiceProviders\Multitexter;

use App\ServiceProviders\IServiceProvider;
use App\ServiceProviders\General\BaseSendSms;


use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSms extends BaseSendSms implements IServiceProvider{

    use Multitexter;

    protected $purchase_endpoint = '/sendsms';
    protected $purchase_action = 'POST';

    protected $purchase_endpoint2 = '/sms';
    protected $purchase_action2 = 'GET';

    protected $status_endpoint = '/';
    protected $status_action = 'GET'; 

    public function mapStandardToAdapterRequest() 
    {
        $this->providerRequest['sender_name'] = $this->standardPayload['sender'] ?? 'Redundancy';
        $this->providerRequest['recipients'] = $this->standardPayload['phonenumber'];
        $this->providerRequest['message'] = $this->standardPayload['message'];
        $this->providerRequest['forcednd'] = 1;

        $this->providerRequest['email'] = $this->getUsername();
        $this->providerRequest['password'] = $this->getPassword();
    }

    public function setAdditionalHeaders() 
    {
        $headers = array_merge($this->additionalHeaders, [
            'Authorization' => $this->getApiKey(),
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
        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, "Sms status unknown");
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_PENDING;
    }

    public function handleFailedResponse($response) 
    {
        $data = $response->json();
        $message = "Sms failed";

        if(isset($data['msg'])) {
            $message = $data['msg'] ?? 'Sms failed';
        }

        $this->finalResponseDto = new FinalResponseDto(true, ErrorCode::PROVIDER_FAILED_TRANSACTION, $message);
        $this->finalResponseDto->debit_business = IServiceProvider::DO_NOT_DEBIT;
        $this->finalResponseDto->status = IServiceProvider::TRANSACTION_FAILED;
    }  
    
    public function determineTransactionStatus($response) 
    {
        $data = $response->json();

        if($response->successful())
        {
            if(isset($data['status']))
            {
                if((int)$data['status']== 1) 
                {
                    // Success
                    $this->setProviderTransactionStatus('SUCCESS');
                    $this->handleSuccessResponse($response);
                }else{
                    // FAILED
                    $this->setProviderTransactionStatus('FAILED');
                    $this->handleFailedResponse($response);
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
            $this->setProviderTransactionStatus('FAILED');
            $this->handleFailedResponse($response);
        }
    }

    public function getProviderReference($response)
    {
        $data = $response->json();
        $provider_reference = null;

        if(isset($data['status']) && ((int)$data['status']== 1)) {
            $provider_reference = $data['msgid'];
        }

        return $provider_reference;
    }

    public function mockSuccessRequest()
    {
        return Http::fake([
                '*' => Http::response( [
                        "status" => 1,
                        "msgid" => "msg_662eaec7c8985",
                        "units" => 2,
                        "balance" => "517.00",
                        "msg" => "Message has been sent"
                    ])], 200, ['Headers'])
                    ->acceptJson()
                    ->post('https://test.com', []);
    }

    public function  mockFailedRequest()
    {
        return  Http::fake(['*' => Http::response([
                        "msg"=> "Unauthenticated.",
                        "status" => -2
                    ])], 400, ['Headers'])
                ->acceptJson()
                ->post('https://test.com', []);
    }

    public function mockPendingRequest()
    {
        return Http::fake(['*' => Http::response([])], 500, ['Headers'])
            ->acceptJson()
            ->post('https://test.com', []);
    }
}