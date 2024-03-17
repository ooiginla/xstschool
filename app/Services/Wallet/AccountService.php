<?php

namespace App\Services\Wallet;

use App\Models\Account;

class AccountService
{

    public function createAgentAccount($account_name, $account_type, $currency="NGN")
    {
        $account = new Account;
        $account->account_name = $account_name;
        $account->account_type = $account_type;
        $account->currency = $currency;
        $account->save();

        return $account;
    }
}