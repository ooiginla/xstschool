<?php

namespace App\Transformers\Bulksmsnigeria;

use App\Transformers\ITransformer;
use App\Transformers\BaseTransformer;

use App\Exceptions\ErrorCode;
use App\ServiceProviders\FinalResponseDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\BusinessIntegration;
use App\Models\Provider;
use App\Models\Business;

use App\Exceptions\InternalAppException;

class SendSms extends BaseTransformer implements ITransformer
{
   protected $provider_code = 'Bulksmsnigeria';

   public function transformPurchaseRequest($request)
   {
      $data['phonenumber'] = $request->input('to');
      $data['sender'] = $request->input('from');
      $data['message'] = $request->input('body');
      $data['dnd_override'] = $request->input('dnd');
      $data['client_ref'] = $this->generateRandomRef($request->business->username);

      $request->merge($data);

      return $request;
   }

   public function authenticate($request)
   {
      $token = $request->input('api_token');

      $provider = Provider::where('code', $this->provider_code)->first();

      $businessIntg = BusinessIntegration::where('provider_id', $provider->id)
                        ->where('public_api_key', $token)
                        ->first();
      
      if(empty($businessIntg)){
         throw new InternalAppException(ErrorCode::INVALID_INTEGRATION_CREDENTIAL);
      }

      $business = Business::find($businessIntg->business_id);

      $request->merge(['business' => $business]);
      
      return $request;
   }

   public function transformPurchaseResponse($response)
   {
      /*
         "data": {
            "status": "success",
            "message": "Message Sent",
            "message_id": "bb1db189-1257-4dcb-bf0a-b0394234aa57",
            "cost": 2.49,
            "currency": "NGN",
            "gateway_used": "direct-refund"
         }
      */
     //  ErrorException: Undefined variable $data in file /Users/ooiginla/Sites/hubsupport/app/Transformers/Bulksmsnigeria/SendSms.php on line 75

      $payload = $response->getOriginalContent();
      
      $txnstatus =  $payload['data']['transaction_status'];
      $httpcode = $txnstatus != 'SUCCESS' ? 500:200;

      $data = [];
      $data['status'] = $txnstatus =="SUCCESS" ? 'success':'failed';
      $data['message'] = $txnstatus == "SUCCESS" ? 'Message Sent':'Message Failed';
      $data['message_id'] = $payload['data']['provider_reference'];
      $data['cost'] = (float) $payload['data']['amount'];
      $data['currency'] = $payload['data']['currency'];
      $data['gateway_used'] = "direct-refund";

      return  response()->json(['data' => $data], $httpcode);
   }   
}