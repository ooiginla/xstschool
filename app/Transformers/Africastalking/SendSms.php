<?php

namespace App\Transformers\Africastalking;

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
   protected $provider_code = 'Africastalking';


   public function transformPurchaseRequest($request)
   {
      $data['phonenumber'] = $request->input('to');
      $data['sender'] = $request->input('from');
      $data['message'] = $request->input('message');
      $data['dnd_override'] = $request->input('dnd');
      $data['client_ref'] = $this->generateRandomRef($request->business->username);

      $request->merge($data);

      return $request;
   }

   public function authenticate($request)
   {
      $token = $request->header('apiKey');

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
        {
            "SMSMessageData": {
               "Message": "Sent to 0/1 Total Cost: 0",
               "Recipients": [
                     {
                        "cost": "0",
                        "messageId": "None",
                        "number": "07035361770",
                        "status": "InvalidPhoneNumber",
                        "statusCode": 403
                     }
               ]
            }
         }
      */
     //  ErrorException: Undefined variable $data in file /Users/ooiginla/Sites/hubsupport/app/Transformers/Bulksmsnigeria/SendSms.php on line 75

      $payload = $response->getOriginalContent();
      
      // Exceptions
      if (!$payload['status']) {
         $msg = $payload['message'];
         $final_data = [];
      }
      
      $txnstatus =  $payload['data']['transaction_status'] ?? null;
      $httpcode = 201;

      $randProvRef = $this->generateRandomString(32,true,true);

      if($txnstatus == 'SUCCESS'){
         $msg = "Sent to 1/1 Total Cost: NGN 2.2000";
         $final_data = [
            "cost" => "NGN 2.2000",
            "messageId" => "ATXid_".$randProvRef,
            "messageParts" => 1,
            "number" => $payload['data']['value_number'],
            "status" => "Success",
            "statusCode" => 101
         ];
      }

      if($txnstatus == 'FAILED'){
         $msg = "Sent to 0/1 Total Cost: 0";
         $final_data = [
            "cost" => "0",
            "messageId" => "None",
            "number" => $payload['data']['value_number'],
            "status" => "InvalidPhoneNumber",
            "statusCode" => 501
         ];
      }

      if($txnstatus == 'PENDING'){
         $msg = "Sent to 0/1 Total Cost: 0";
         $final_data = [
            "cost" => "0",
            "messageId" => "None",
            "number" => "07035361770",
            "status" => "Queued",
            "statusCode" => 102
         ];
      }

      $data = [];
      $data['SMSMessageData']['Message'] = $msg;
      
      if (!empty($final_data)) {
         $data['SMSMessageData']['Recipients'][] = $final_data;
      }else{
         $data['SMSMessageData']['Recipients'] = [];
      }

      return  response()->json($data, $httpcode);
   }   
}