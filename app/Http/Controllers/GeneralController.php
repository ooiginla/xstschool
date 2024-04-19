<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Notifications\Sms\SmsService;
use App\Services\Requests\ApiRequestDto;
use App\Models\RequestLog;
use App\Models\Request as ApiRequest;
use App\Exceptions\InternalAppException;
use App\Exceptions\ErrorCode;
use App\Services\Requests\ApiRequestService;
use App\Models\Account;
use App\Services\Responses\BusinessResponseDto;

class GeneralController extends Controller
{
    public function retry(Request $request, ApiRequestDto $apiRequestDto, SmsService $service,)
    {
        $request->validate([
            'client_ref' => 'required'
        ]);

        $data = $request->all();
        $data['retry_action'] = true;

        $apiRequest = $this->retrieveRequest($data['business']->id, $data['client_ref']);

       
        if(empty($apiRequest)){
            throw new InternalAppException(ErrorCode::TRANSACTION_NOT_FOUND);
        }

        $service = $this->resolveService($apiRequest->service->path);

        $data['retry_action'] = true;

        return $service->process($data, $apiRequestDto);
    }

    protected function resolveService($path)
    {
        return new $path(new ApiRequestService());
    }

    protected function retrieveRequest($business_id, $client_ref)
    {
        $apiRequest = ApiRequest::where('business_id', $business_id)
                        ->where('client_ref', $client_ref)
                        ->first();
        
        if(empty($apiRequest)){
            throw new InternalAppException(ErrorCode::TRANSACTION_NOT_FOUND);
        }

        return $apiRequest;
    }

    public function status(Request $request)
    {
        $request->validate([
            'client_ref' => 'required'
        ]);

        $data = $request->all();

        $apiRequest = $this->retrieveRequest($data['business']->id, $data['client_ref']);
        $service = $this->resolveService($apiRequest->service->path);

        return $service->getStatus($apiRequest);
    }

    public function getBalance(Request $request)
    {
        $data = $request->all();

        $account = Account::where('business_id', $data['business']->id)->first();

        $dto = new BusinessResponseDto(true, ErrorCode::SUCCESSFUL , 'Balance successfully retrieved');
        $dto->provider_data = [];
        $dto->http_code = 200;
        
        $data = [
            'account_number' => $account->account_no,
            'account_name' => $account->account_name,
            'currency' => $account->currency,
            'balance' => number_format((float)$account->balance, 2, '.', ''),
            'account_type' => ((int)$account->account_type == 1) ? 'REGULAR':'GL',
            'overdraw_allowed' => ((int) $account->can_overdraw) ? true:false,
            'overdraw_limit' => $account->overdraw_limit,
            'liened_amount' => $account->liened_amount,
            'last_updated' => $account->updated_at
        ];

        $dto->data = $data;

        return $this->sendFinalResponse($dto);
    }
}