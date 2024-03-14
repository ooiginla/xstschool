<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use App\Services\Requests\RequestService;
use Illuminate\Support\Str;

class TransactionService {

   public function logTransaction($transactionDto)
   {
        $apiRequest = RequestService::where('business_id', $transactionDto->business->id)
                                ->where('request_id', $transactionDto->request_id)
                                ->first();

        if(!empty($transaction)) {
            $apiRequest = new RequestService;
            $transaction->request_id = $transactionDto->request_id;
            $transaction->business_id = $transactionDto->business->request_id;
            $transaction->service_id = $transactionDto->service->id;
            $transaction->reference = $this->generateReference($$transactionDto->service->code);
            $transaction->currency = $transactionDto->currency;
            $transaction->narration = $transactionDto->narration;
            $transaction->category = $transactionDto->category;
            $transaction->type = $transactionDto->type;
            $transaction->amount = $transactionDto->amount;
            $transaction->status = Status::PENDING;
            $transaction->save();
        }

        $transactionDto->transaction = $transaction;

        return $transactionDto;
   }

   public function generateReference($service_code = 'REF')
   {
       return 'OYS' . '_' . $service_code . '_' . time() . '_' . strtoupper(Str::random(5));
   }
}