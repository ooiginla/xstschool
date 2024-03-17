<?php

namespace App\Services;

use App\Models\Service;
use App\Services\Requests\ApiRequestService;
use App\Services\Requests\ApiRequestDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ServiceProvider;
use App\Models\ProviderTransaction;
use App\Models\Provider;
use App\Models\Transaction;
use App\Exceptions\InternalAppException;
use App\Services\Wallet\WalletService;
use App\Services\Wallet\Ledger;
use App\Services\Transactions\TransactionDto;
use App\Services\Transactions\Status;
use Illuminate\Http\Client\ConnectionException;

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
    protected $adapterResponse;
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

        // Lien Balance
        $this->lienBalance();
    }

    private function lienBalance()
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

        $providerTxn = $this->logProviderRequest($serviceProvider);
        
        $adapterResponse = null;

        try{

            $adapterResponse = Http::fake([
                'testing.com/*' => Http::response(['foo' => 'bar'], 200, ['Headers']),
            ])
            ->acceptJson()
            ->withToken($this->getToken())
            ->post( $serviceProvider->adapter_url, $this->adapterRequestDto);

        }catch(ConnectionException $ex){
            //...handle here
        }
        
        $this->logProviderResponse($providerTxn, $adapterResponse);


        $this->debitTransaction();
    }

    public function getCoreTransactionDto()
    {
        $transactionDto =  new TransactionDto;
        $transactionDto->request_id = $this->transaction->id;
        $transactionDto->business_id = $this->requestPayload['business']->id;
        $transactionDto->reference = $this->transaction->oystr_ref;
        $transactionDto->value_number = $this->getValueNumber();
        $transactionDto->currency = $this->getCurrency();
        $transactionDto->narration = $this->getNarration();
        $transactionDto->category = 'api-request';
        $transactionDto->type = 'DEBIT';
        $transactionDto->amount = $this->transaction->client_price;
        $transactionDto->status = Status::PENDING;

        return $transactionDto;
    }

    private function debitTransaction()
    {
        $transactionDto = $this->getCoreTransactionDto();
        $transactionRecord = $this->createTransactionRecord($transactionDto);


        $debitLedger = new Ledger('DEBIT', '0000000001', $this->transaction->client_price, 
        'payment for Api purchase', 'REGULAR');

        $creditLedger = new Ledger('CREDIT', '0000000002', $this->transaction->client_price, 
        'payment for Api purchase', 'REGULAR');

        $walletService = new WalletService();

        $walletResponse = $walletService->post(
            $this->requestPayload['business']->id,
            1,
            $this->transaction->oystr_ref,
            $this->transaction->client_price,
            [$debitLedger, $creditLedger]
        );

        dd($walletResponse);
    }

    public function createTransactionRecord($transactionDto)
    {
        $transaction = new Transaction;
        $transaction->request_id = $transactionDto->request_id;
        $transaction->business_id = $transactionDto->business_id;
        $transaction->reference = $transactionDto->reference;
        $transaction->value_number = $transactionDto->value_number;
        $transaction->currency = $transactionDto->currency;
        $transaction->narration = $transactionDto->narration;
        $transaction->category = $transactionDto->category;
        $transaction->type = $transactionDto->type;
        $transaction->amount = $transactionDto->amount;
        $transaction->status = $transactionDto->status;
        $transaction->save();

        return $transaction;
    }
    
    public function logProviderResponse($providerTxn, $adapterResponse)
    {
        $providerTxn->standard_response = $adapterResponse?->body();
        $providerTxn->save();
    }

    public function logProviderRequest($serviceProvider)
    {
        $providerTxn = new ProviderTransaction;
        $providerTxn->request_id = $this->transaction->id;
        $providerTxn->service_id = $serviceProvider->service_id;
        $providerTxn->provider_id = $serviceProvider->provider_id;
        $providerTxn->standard_request = json_encode($this->adapterRequestDto);
        $providerTxn->save();

        return $providerTxn;
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
        Cache::pull($this->service_name);
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