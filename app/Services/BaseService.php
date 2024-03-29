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
use App\Models\Account;
use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode;
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
    protected $walletService;
    protected $apiRequestDto;
    protected $adapterRequestUrl;
    protected $adapterRequestDto;
    protected $adapterResponse;
    protected $serviceReturnedData = [];
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
        $this->walletService = new WalletService();
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

    public function callServiceProvider()
    {
        // Has final status previously? Don't call provider again
        if ($this->transaction->request_status != Status::PENDING) {
            $this->adapterResponse = json_decode($this->transaction?->providerTransaction?->standard_response, true);
            return;
        }


        $this->loadProvidersIntoCache();
        $serviceProvider = $this->chooseAdapter();

        if(empty($serviceProvider)){
            throw new InternalAppException(ErrorCode::NO_PROVIDER_ACTIVE);
        }

        // Log provider request
        $providerTxn = $this->logProviderRequest($serviceProvider);

        // Update Provider Txn on Request Object
        $this->transaction->provider_transaction_id = $providerTxn->id;
        $this->transaction->save();

        $adapterResponse = null;

        try{
            $adapterResponse = Http::fake([
                'testing.com/*' => Http::response([
                    'status' => 'SUCCESS',
                    'response_code' => 'SUCCESSFUL',
                    'response_message' => 'Sms successfully sent',
                    'debit_business' => 'YES',
                    'provider_raw_data'=>'a4apple',
                    'data' => []
                ], 200, ['Headers']),
            ])
            ->acceptJson()
            ->withToken($this->getToken())
            ->post( $serviceProvider->adapter_url, $this->adapterRequestDto);

            $adapterResponse = $adapterResponse->json();

        }catch(ConnectionException $ex){
            throw new InternalAppException(ErrorCode::CONNECTION_TIMEOUT);
        }catch(Exception $ex){
            throw new InternalAppException(ErrorCode::PROVIDER_UNREACHABLE);
        }
        
        // Update provider response
        $this->logProviderResponse($providerTxn, $adapterResponse);

        $this->adapterResponse = $adapterResponse;

        if($this->shouldDebitBusiness($adapterResponse)) 
        {
            // Debit Transaction
            $walletResponse = $this->debitTransaction($is_api_request = true);

            if ($walletResponse->isSuccessful())
            {
                $this->transaction->payment_status = Status::PAID;
                $this->transaction->debited = true;
            }elseif($walletResponse->isFailed()){
                $this->transaction->payment_status = Status::FAILED;
            }else{
                // $this->transaction->payment_status = Status::PENDING;
            }

            $this->transaction->save();
            $this->transaction->refresh();
        }else{

        }

        $this->updateRequestWithAdapterResponse();   

        $this->unlienRequest();
    }

    public function unlienRequest()
    {
        // Unlien Request: is liened, has final status, our debit attempt did not fail
        if ($this->transaction->liened 
                && ($this->transaction->request_status == Status::FAILED || $this->transaction->request_status == Status::SUCCESS)
                && ($this->transaction->payment_status != Status::FAILED)
        ) {
            $this->walletService->unlienRequest($this->transaction);
        }
    }

    public function updateRequestWithAdapterResponse()
    {
        if(! empty($this->adapterResponse) && array_key_exists($this->adapterResponse['response_code'], ErrorCode::CODES))
        {
            $this->transaction->request_status = $this->adapterResponse['status'];
            $this->transaction->response_code = ErrorCode::CODES[$this->adapterResponse['response_code']]['code'];
            $this->transaction->response_message = $this->adapterResponse['response_message'] ?? ErrorCode::CODES[$this->adapterResponse['response_code']]['message'];
        }else{
            $this->transaction->response_code = ErrorCode::CODES['PROVIDER_UNKNOWN_RESPONSE']['code'];
            $this->transaction->response_message = ErrorCode::CODES['PROVIDER_UNKNOWN_RESPONSE']['response_message'];
            
            // Log a buginfo
        }
                
        $this->transaction->save();
    }

    public function shouldDebitBusiness($adapterResponse)
    {
        if ($adapterResponse['debit_business'] == 'YES') {
            return true;
        }
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

    public function getDebitLedger()
    {
        $account = Account::where('business_id',$this->transaction->business_id)->first();

        if(empty($account)){
            throw new Exception('Debit Account: Business wallet account not setup');
        }

        return new Ledger(
            'DEBIT', 
            $account->account_no, 
            $this->transaction->client_price, 
            $this->getNarration(), 
            'REGULAR'
        );
    }

    public function getCreditLedger()
    {

        $account = $this->getServiceModel()->subcategory->account;

        if(empty($account)){
            throw new Exception('Credit Account: Internal Wallet account not setup');
        }

        return new Ledger(
            'CREDIT', 
            $account->account_no, 
            $this->transaction->client_price, 
            $this->getNarration(), 
            'REGULAR'
        );
    }

    private function debitTransaction($is_api_request)
    {
        if(! $this->transaction->payment_status == Status::PENDING) {
            // Log We lost Money....
            return;
        }

        $transactionDto = $this->getCoreTransactionDto();
        $transactionRecord = $this->createTransactionRecord($transactionDto);

        $debitLedger = $this->getDebitLedger();
        $creditLedger = $this->getCreditLedger();

        $walletResponse = $this->walletService->post(
            $this->requestPayload['business']->id,
            1,
            $this->transaction->oystr_ref,
            $this->transaction->client_price,
            [$debitLedger, $creditLedger]
        );

        if($walletResponse->isSuccessful()) {
            // Update Request:
            $transactionRecord->status = Status::SUCCESS;
            $transactionRecord->save();
        }
        
        if(! $is_api_request && $walletResponse->isFailed()) {
            // Update Request:
            $transactionRecord->status = Status::FAILED;
            $transactionRecord->save();
        }  

        return $walletResponse;
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
        $providerTxn->standard_response = json_encode($adapterResponse);
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

    public function sendFinalResponse()
    {
        $this->transaction->refresh();

        $response['status'] = true;
        $response['code'] = $this->transaction->response_code;
        $response['message'] = $this->transaction->response_message;
        $response['data'] = $this->serviceReturnedData ?? [];
        $response['provider_data'] = $this->adapterResponse['provider_raw_data'] ?? [];


        return response()->json($response, 200);
    }

    public function processData($data)
    {
        $this->setRequestPayload($data);
        $this->mapPayloadToRequestDto();
        $this->logTransaction();
        $this->prepareAdapterRequest();
        $this->callServiceProvider();
        $this->handleProviderResponse();

        return $this->sendFinalResponse();
    }
}