<?php

namespace App\ServiceProviders\General;

use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use App\ServiceProviders\BaseServiceProvider;

class BaseSendSms extends BaseServiceProvider 
{
    // Generic Sms Status
    public function validateStatusParams()
    {
        $validated = true;
        return $validated;
    }

    public function mapStandardToAdapterRequestStatus() 
    {
        $this->providerRequest['reference'] = '';
    }


    // Generic Sms Validation
    public function validateParams()
    {
        $validated = true;

        if(!isset($this->standardPayload['phonenumber'])) {
            $validated = false;
            $this->finalResponseDto = new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "phonenumber field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['message'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "message field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['sender'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "sender field is empty");
            return $validated;
        }

        return $validated;
    }

   
}