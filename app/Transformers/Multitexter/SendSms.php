<?php

namespace App\Transformers\Multitexter;

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
   protected $provider_code = 'Multitexter';

   // https://app.multitexter.com/v2/app/sendsms

   public function transformPurchaseRequest($request)
   {
      $data['phonenumber'] = $request->input('recipients');
      $data['sender'] = $request->input('sender_name');
      $data['message'] = $request->input('message');
      $data['dnd_override'] = $request->input('forcednd');
      $data['client_ref'] = $this->generateRandomRef($request->business->username);

      $request->merge($data);

      return $request;
   }

   public function authenticate($request)
   {
      $token = $request->header('Authorization');

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
      $payload = $response->getOriginalContent();
      $httpcode = 200;
      $msg = $payload['message'] ?? 'Unknown Error';

       // Exceptions
       if (!$payload['status']) {
         $data = [];
         $httpcode = 400;
      }
      
      $txnstatus =  $payload['data']['transaction_status'] ?? null;
      

      $randProvRef = $this->generateRandomString(13,true,true);

      if($txnstatus == 'SUCCESS'){
         $data = [
            "status" => 1,
            "msgid" => "msg_".$randProvRef,
            "units" => 2,
            "balance" => "0.00",
            "msg" => $msg
        ];
      }

      if($txnstatus == 'FAILED'){
         $data =[
            "msg"=> $msg,
            "status" => -2
        ];
        $httpcode = 400;
      }

      if($txnstatus == 'PENDING'){
         $data = [
            "msg"=> $msg,
            "status" => -10
        ];
      }

      // Exceptions
      if(empty($txnstatus)) {
         $data =[
            "msg"=> $msg,
            "status" => -2
         ];
      }

      return  response()->json($data, $httpcode);
   }   
}