<?php

namespace App\ServiceProviders\Termii;

use App\ServiceProviders\IServiceProvider;
use App\ServiceProviders\General\BaseSendSms;


use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSms extends BaseSendSms implements IServiceProvider{

    use Termii;

    protected $purchase_endpoint = '/sms/send';
    protected $purchase_action = 'POST';

    protected $status_endpoint = '/';
    protected $status_action = 'GET'; 

    public function mapStandardToAdapterRequest() 
    {        
        $this->providerRequest['to'] = $this->standardPayload['phonenumber'];
        $this->providerRequest['from'] = $this->standardPayload['sender'] ?? 'serviceswap';
        $this->providerRequest['sms'] = $this->standardPayload['message'];
        $this->providerRequest['type'] = "plain";
        $this->providerRequest['channel'] = "generic";
        $this->providerRequest['api_key'] = $this->getApiKey();
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

        if(isset($data['message'])) {
            $message = $data['message'] ?? 'Sms failed';
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
            if(isset($data['code']) && $data['code'] == "ok")
            {
                // Success
                $this->setProviderTransactionStatus('SUCCESS');
                $this->handleSuccessResponse($response);
            }else{
                // PENDING: BAD RESPONSE
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
        $data = $response->json();
        $provider_reference = null;

        if(isset($data['code']) && $data['code'] == "ok") {
            $provider_reference = $data['message_id'];
        }

        return $provider_reference;
    }

    public function mockSuccessRequest()
    {
        return Http::fake([
                '*' => Http::response( [
                            "code" => "ok",
                            "message_id" => "102024042915252000000099412",
                            "message_id_str" => "102024042915252000000099412",
                            "message" => "Successfully Sent",
                            "balance" => 2796.7,
                            "user" => "Omotayo Iginla"
                    ])], 200, ['Headers'])
                    ->acceptJson()
                    ->post('https://test.com', []);
    }

    public function  mockFailedRequest()
    {
        return  Http::fake(['*' => Http::response([
                        "message" => "Invalid Sender id"
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