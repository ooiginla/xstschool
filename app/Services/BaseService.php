<?php

namespace App\Services;

use App\Models\Service;
use App\Services\Requests\ApiRequestService;
use App\Services\Requests\ApiRequestDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\ServiceProvider;
use App\Models\ProviderTransaction;
use App\Models\BusinessIntegration;
use App\Models\Provider;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\RequestLog;
use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode;
use App\Services\Wallet\WalletService;
use App\Services\Wallet\Ledger;
use App\Services\Transactions\TransactionDto;
use App\Services\Transactions\Status;
use App\Services\Transactions\Action;
use Illuminate\Http\Client\ConnectionException;
use App\Services\Responses\BusinessResponseDto;

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
       $retrieved_data =  $this->logRequestPayload($data);

        if (isset($data['retry_action'])) {
            $this->requestPayload = $retrieved_data;
        }else{
            $this->requestPayload = $data;
        }
        
        $this->apiRequestDto = new ApiRequestDto;
        $this->walletService = new WalletService();
    }

    public function logRequestPayload($data)
    {
        $payload = $data;
        $payload['business'] = $payload['business']->id;
        $client_ref = $payload['client_ref'];

        if (!empty($client_ref) && isset($payload['retry_action']) && $payload['retry_action']) 
        {
            $request_log = RequestLog::where('business_id', $payload['business'])
                                ->where('client_ref', $client_ref)
                                ->first();

            if(empty($request_log)){
                throw new InternalAppException(ErrorCode::TRANSACTION_NOT_FOUND);
            }
            
            $request_data = json_decode($request_log->client_request, true)['payload'];
            $request_data['business'] = Business::find($request_data['business']);
            $request_data['retry_action'] = true;

            return $request_data;
        }

        $data = [
            'payload' =>  $payload,
            'headers' => app('request')->header(),
            'ip' => app('request')->ip()
        ];

        $request_log = RequestLog::create([
            'business_id' => $payload['business'],
            'client_ref' => $client_ref ?? '',
            'client_request' => json_encode($data)
        ]);

        return null;
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
        $this->apiRequestDto->retry_action = $this->requestPayload['retry_action'] ?? false;
        $this->transaction = $this->apiRequestService->logRequest($this->apiRequestDto);
    }

    public function resetTransactionForReprocessing()
    {

    }

    protected function prepareGetStatusAdapterRequest()
    {
        $this->adapterRequestDto = [
            'transaction_id' => $this->transaction->oystr_ref
        ];

        return $this->adapterRequestDto;
    }

    public function callServiceProvider($action = Action::PURCHASE)
    {
        // Has final status previously? Don't call provider again
        if ($this->transaction->request_status != Status::PENDING) {
            $this->adapterResponse = json_decode($this->transaction?->providerTransaction?->standard_response, true);
            return;
        }

        // FOR PURCHASES
        if ($action == Action::PURCHASE) {
            $this->loadProvidersIntoCache();

            $selectedProvider = $this->chooseAdapter($this->serviceObject->name);

            if (empty($selectedProvider)) {
                throw new InternalAppException(ErrorCode::NO_PROVIDER_ACTIVE);
            }

            // UpdateAdapter payload
            $this->adapterRequestDto['provider'] = $selectedProvider['code'];
            $this->adapterRequestDto['integration_id'] = $selectedProvider['integration_id'];
            $this->adapterRequestDto['service_name'] = $this->serviceObject->name;
            $this->adapterRequestDto['sa_business'] =  $this->requestPayload['business']->username;
            $this->adapterRequestDto['mock_response'] =  'failed';

             // Log provider request
            $providerTxn = $this->logProviderRequest(
                                $this->serviceObject->id, 
                                $selectedProvider['provider_id'], 
                                $selectedProvider['integration_id'], 
                                $action
                            );
            
            $endpoint = 'http://127.0.0.1:8001/api/v1/adapters/purchase';
        }

        // FOR GET STATUS
        if ($action == Action::GET_STATUS) {

            // Check if implementation of get status does not exists..
            if (empty($serviceProvider->status_url)) {
                $this->adapterResponse = json_decode($this->transaction?->providerTransaction?->standard_response, true);
                return;
            }
            
            $provider_transaction = $this->transaction->providerTransaction;
            
            $this->adapterRequestDto = $this->prepareGetStatusAdapterRequest();

            // UpdateAdapter payload
            $this->adapterRequestDto['provider'] = $serviceProvider->provider->code;
            $this->adapterRequestDto['integration_id'] = $serviceProvider->provider->code;
            $this->adapterRequestDto['service_name'] = $serviceProvider->service->name;
            $this->adapterRequestDto['sa_business'] =  $this->requestPayload['business']->username;

            // Log provider request
            $providerTxn = $this->logProviderRequest(
                                $provider_transaction->service_id,
                                $provider_transaction->provider_id,
                                $provider_transaction->business_integration_id,
                                $action
                            );
            
            $endpoint = 'http://testing.com/adapter/notification/sms/status';
        }

        $this->adapterRequestDto['provider_transaction_id'] = $providerTxn->id;

        // Update Provider Txn on Request Object
        $this->transaction->provider_transaction_id = $providerTxn->id;
        $this->transaction->save();

        $adapterResponse = null;
        
        try{
            /*
            $success = [
                'testing.com/*' => Http::response([
                    'status' => 'SUCCESS',
                    'response_code' => 'SUCCESSFUL',
                    'response_message' => 'Sms Sent',
                    'debit_business' => 'YES',
                    'provider_raw_data'=>'a4apple',
                    'data' => []
                ])
            ];

            $pending = [
                'testing.com/*' => Http::response([
                    'status' => 'PENDING',
                    'response_code' => 'CONNECTION_TIMEOUT',
                    'response_message' => 'Connection Timeout',
                    'debit_business' => 'NO',
                    'provider_raw_data'=>'a4apple',
                    'data' => []
                ])
            ];

            $failure = [
                'testing.com/*' => Http::response([
                    'status' => 'FAILED',
                    'response_code' => 'TRANSACTION_NOT_FOUND',
                    'response_message' => 'Sms not sent',
                    'debit_business' => 'NO',
                    'provider_raw_data'=>'a4apple',
                    'data' => []
                ])
            ];

            $adapterResponse = Http::fake($success, 200, ['Headers'])
                                ->acceptJson()
                                ->withToken($this->getToken())
                                ->post($endpoint, $this->adapterRequestDto);
            
                DELETE FROM wallet_transactions;
                DELETE FROM transactions;
                DELETE FROM retries;
                DELETE FROM requests;
                DELETE FROM request_logs;
                DELETE FROM provider_transactions;
                DELETE FROM histories;
            */

            $adapterResponse = Http::acceptJson()
                                    ->withToken($this->getToken())
                                    ->post($endpoint, $this->adapterRequestDto);

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
            $this->transaction->response_message = $this->adapterResponse['response_message'] ?? ErrorCode::CODES[$this->adapterResponse['response_code']]['message'];
            
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
        $transactionDto->business_id = $this->transaction->business_id;
        $transactionDto->reference = $this->transaction->oystr_ref;
        $transactionDto->value_number = $this->transaction->value_number;
        $transactionDto->currency = $this->getCurrency();
        $transactionDto->narration = $this->transaction->narration;
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
            $this->transaction->narration, 
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
            $this->transaction->narration,
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
            $this->transaction->business_id,
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
        $providerTxn->status = $adapterResponse['status'] ?? 'PENDING';
        $providerTxn->standard_response = json_encode($adapterResponse);
        $providerTxn->save();
    }

    public function logProviderRequest($service_id, $provider_id, $integration_id, $action)
    {
        $providerTxn = new ProviderTransaction;
        $providerTxn->request_id = $this->transaction->id;
        $providerTxn->own_reference = $this->transaction->oystr_ref;
        $providerTxn->service_id = $service_id;
        $providerTxn->provider_id = $provider_id;
        $providerTxn->business_integration_id = $integration_id;
        $providerTxn->action = $action;
        $providerTxn->standard_request = json_encode($this->adapterRequestDto);
        $providerTxn->save();

        return $providerTxn;
    }

    public function getToken()
    {
        return config('app.env'). '_' . '4d66a5b1-0b25-4248-b833-50396d19aab2';
    }

    public function chooseAdapter($service_name, $retry = false)
    {   
        // Get all integration owners of the service
        $providers = Cache::get($this->service_name);

        // internal_providers
        $internal_providers = array_filter($providers, function ($k){
                return (str_starts_with($k['owner'], 'INTERNAL'));
        });

        // client_providers
        $client_providers = array_filter($providers, function ($k){
            return(str_starts_with($k['owner'], 'CLIENT'));
        });

        // USE ONLY INTERNAL FOR NOW
        $providers = $internal_providers;

        $max_provider = null;
        $max_success_rate = 0;


        // GET BEST SUCCESS RATE
        foreach ($providers as $provider) {
            if ($provider['success_rate'] >= $max_success_rate && $provider['status']) {
                $max_success_rate = $provider['success_rate'];
                $max_provider = $provider;
            }
        }
        
        return $max_provider;
        /*
            'SERVICE_NAME':[
                'PROVIDER' => [
                    'success': 20
                    'failure': 4,
                    'status': active,
                    'success_rate': 80
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
            $services = Service::get();

            foreach($services as $service) 
            {
                $data=[];

                // Get Providers (goes in cache later)
                $service_providers = ServiceProvider::where('service_id', $service->id)->get();

                foreach($service_providers as $service_provider) 
                {
                    $data[] = [
                        'owner' => 'INTERNAL',
                        'code' => $service_provider->provider->code,
                        'success' => 0, 
                        'failure' => 0, 
                        'success_rate' => 100, 
                        'status' => true, 
                        'last_updated' => time(),
                        'provider_id' => $service_provider->provider_id,
                        'integration_id' => $service_provider->integration_id,
                    ];
                }

                // get all provider ids for the service.
                $provider_id = $service_providers->pluck('provider_ids'); 

                // Check client has integration to provider for that service
                $clientInts = BusinessIntegration::select('businesses.username as business_name','providers.code as provider_code')
                                        ->join('providers', 'business_integrations.provider_id', 'providers.id')
                                        ->join('businesses', 'business_integrations.business_id', 'businesses.id')
                                        ->where('business_id', $this->requestPayload['business']->id)
                                        ->whereIn('provider_id', $provider_id)->get();
                
                foreach($clientInts as $ints)
                {
                    $data[] = [
                        'owner' => 'CLIENT_'.$ints->business_name,
                        'code' => $ints->provider_code,
                        'success' => 0, 
                        'failure' => 0, 
                        'success_rate' => 100, 
                        'status' => true, 
                        'last_updated' => time(),
                        'provider_id' => $int->provider_id,
                        'integration_id' => $int->id,
                    ];
                }
            }       

            Cache::put($this->service_name, $data);
        }
    }

    public function sendFinalResponseDto()
    {
        $this->transaction->refresh();

        $dto = new BusinessResponseDto(true, $this->transaction->response_code, $this->transaction->response_message);
        $dto->provider_data = $this->adapterResponse['provider_raw_data'] ?? [];
        $dto->http_code = 200;
        
        $service_data = $this->serviceReturnedData ?? [];
        
        $data = [
            'client_reference' => $this->transaction->client_ref,
            'debited' => ((int)$this->transaction->debited) ? true:false,
            'transaction_status' => $this->transaction->request_status,
            'payment_status' => $this->transaction->payment_status,
            'currency' => $this->transaction->currency,
            'amount' => number_format((float)$this->transaction->client_price, 2, '.', ''),
            'action' => 'PURCHASE',
            'provider' => $this->transaction->providerTransaction->provider->code,
            'value_number' => $this->transaction->value_number,
            'narration' => $this->transaction->narration,
            'category' => $this->transaction->category."::".$this->transaction->subcategory,
            'adapter_reference' => $this->transaction->oystr_ref,
            'provider_reference' => $this->transaction->providerTransaction->provider_ref,
            'created_at' => $this->transaction->created_at,
            'service_data' => array_merge([], $service_data),
            'provider_response' => $this->adapterResponse['provider_raw_data'],
        ];

        $dto->data = $data;

        return $dto;
    }

    public function processData($data)
    {
        $this->setRequestPayload($data);
        $this->mapPayloadToRequestDto();
        $this->logTransaction();
        $this->prepareAdapterRequest();
        $this->callServiceProvider();
        $this->handleProviderResponse();
 
        $finalDto = $this->sendFinalResponseDto();

        return $finalDto;
    }

    public function getStatus($apiRequest)
    {
        $this->transaction = $apiRequest;

        $this->walletService = new WalletService();

        // load adapter previous response
        $this->callServiceProvider(Action::GET_STATUS);

        // Map props from adapter to final jsoon
        $this->handleProviderResponse();

        // Send Final Response
        return $this->sendFinalResponse(); 
    }
}