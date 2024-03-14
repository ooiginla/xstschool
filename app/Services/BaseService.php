<?php

namespace App\Services;

use App\Models\Service;
use App\Services\Requests\ApiRequestService;
use App\Services\Requests\ApiRequestDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ServiceProvider;
use App\Models\Provider;
use App\Exceptions\InternalAppException;

class BaseService
{
    protected $service_name = null;
    protected $currency = 'NGN';
    protected $requestPayload = null;
    protected $narration_prefix = 'Purchase';
    protected $category = 'PURCHASE';

    protected $apiRequestService;
    protected $apiRequestDto;
    protected $adapterRequestUrl;
    protected $adapterRequestDto;
    protected $transaction;
    protected $serviceObject;

    public function __construct(ApiRequestService $apiRequestService) {
        $this->apiRequestService = $apiRequestService;
    }

    public function prepareAdapterRequest()
    {
        return [];
    }

    public function getServiceModel()
    {
        if(empty($this->serviceObject)) {
            $this->serviceObject = Service::where('name', $this->service_name)->first();
        }   

        return $this->serviceObject;
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
        $this->transaction = $this->apiRequestService->logRequest($this->apiRequestDto);
    }

    public function lienBalance()
    {
        return true;
    }

    public function callServiceProvider()
    {
        $this->loadProvidersIntoCache();
        $serviceProvider = $this->chooseAdapter();

        if(empty($serviceProvider)){
            throw new InternalAppException(ErrorCode::NO_PROVIDER_ACTIVE);
        }
        
        Http::acceptJson()
                ->withToken($this->getToken())
                ->post( $serviceProvider->adapterRequestUrl, $this->adapterRequestDto);
    }

    public function getToken()
    {
        return config('app.env'). '_' . '4d66a5b1-0b25-4248-b833-50396d19aab2';
    }

    public function chooseAdapter()
    {
        $providers = Cache::get($this->service_name);
        
        $max_provider = null;
        $max_success_rate = 0;

        foreach($providers as $provider => $data){
            if ($data['success_rate'] >= $max_success_rate && $data['status']) {
                $max_success_rate = $data['success_rate'];
                $max_provider = $provider;
            }
        }


        $provider_id = Provider::where('code', $max_provider)->value('id');

        return ServiceProvider::where('service_id', $this->serviceObject->id)
                            ->where('provider_id', $provider_id)
                            ->first();
        /*
            SERVICE_NAME:[
                'PROVIDER' => [
                    'success': 20
                    'failure': 4,
                    'status': active
                ]
            ],
            SERVICE_NAME:['']
        */
    }

    public function loadProvidersIntoCache()
    {
        if (! Cache::has($this->service_name)) 
        {
            $providers = ServiceProvider::select('providers.code')
                            ->join('providers', 'service_providers.provider_id', 'providers.id')
                            ->where('service_id', $this->serviceObject->id)
                            ->get();

            $data = [];

            foreach($providers as $provider){
                $data[$provider->code] = ['success' => 0, 'failure' => 0, 'success_rate' => 100, 'status' => true, 'last_updated' => time(),];
            }
           
            Cache::put($this->service_name, $data);
        }
    }
}