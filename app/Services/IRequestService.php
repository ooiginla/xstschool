<?php

namespace App\Services;

interface IRequestService {

    public function mapPayloadToRequestDto();
    public function prepareAdapterRequest();
    public function handleProviderResponse();
    public function getValueNumber();
    
}