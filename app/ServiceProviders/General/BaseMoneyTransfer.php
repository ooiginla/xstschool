<?php

namespace App\ServiceProviders\General;

use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use App\ServiceProviders\BaseServiceProvider;
use App\ServiceProviders\FinalResponseDto;
use App\Exceptions\ErrorCode;

class BaseMoneyTransfer extends BaseServiceProvider 
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

        if(!isset($this->standardPayload['transfer_type'])) {
            $validated = false;
            $this->finalResponseDto = new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "transfer_type field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['destination_code'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "destination_code field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['destination_account'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "destination_account field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['destination_narration'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "destination_narration field is empty");
            return $validated;
        }

        if(!isset($this->standardPayload['amount'])) {
            $validated = false;
            $this->finalResponseDto =  new FinalResponseDto(false, ErrorCode::INVALID_ADAPTER_PAYLOAD, "amount field is empty");
            return $validated;
        }


        return $validated;
    }

   
}