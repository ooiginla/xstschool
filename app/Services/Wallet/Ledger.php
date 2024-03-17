<?php

namespace App\Services\Wallet;

use App\Models\Account;
class Ledger implements \JsonSerializable
{
    public $account;
    public function __construct(public $action, public $account_no, public $amount, public $narration, public $category)
    {
        $this->amount = round($amount, 2);
        $this->account = Account::where('account_no', $this->account_no)->first();
    }

    public function jsonSerialize()
    {
        return [
            'action' => $this->action,
            'amount' => $this->amount,
            'narration' => $this->narration,
            'category' => $this->category,
            'account_no' => $this->account_no
        ];
    }
}