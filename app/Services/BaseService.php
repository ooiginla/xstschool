<?php

namespace App\Services;

use App\Models\Service;
use App\Services\Requests\ApiRequestService;
use App\Services\Requests\ApiRequestDto;


class BaseService
{
    protected $service_name = null;
    protected $currency = 'NGN';
    protected $requestPayload = null;
    protected $narration_prefix = 'Purchase';
    protected $category = 'PURCHASE';

    protected $apiRequestService;
    protected $apiRequestDto;

    public function __construct(ApiRequestService $apiRequestService) {
        $this->apiRequestService = $apiRequestService;
    }


    public function getServiceModel()
    {
        return Service::where('name', $this->service_name)->first();
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setRequestPayload($data)
    {
        $this->requestPayload = $data;
        $this->apiRequestDto = new ApiRequestDto;
    }

    public function getValueNumber()
    {
        return $this->requestPayload['value_number'] ?? 'NA';
    }

    public function getNarration()
    {
        return $this->narration_prefix . '/'. $this->requestPayload['client_ref'].'/'. $this->getValueNumber();
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function logTransaction()
    {
        // Log Request
        $this->apiRequestService->logRequest($this->apiRequestDto);
    }
}