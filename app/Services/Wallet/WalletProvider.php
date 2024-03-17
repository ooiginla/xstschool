<?php

namespace App\Services\Wallet;

use App\Models\Account;
use App\Services\Wallet\dto\WebhookResult;
use Illuminate\Http\Request;
use App\Services\Wallet\dto\BVN;
use App\Services\Wallet\dto\UserDto;
use App\Services\Wallet\dto\VirtualAccount;

interface WalletProvider
{
    public function getSlug();


    // public function createAccount($data);

    // public function getAllAccount(Provider $provider);

    // public static function getProductName($id);

    // public function constructAccountNo($id);

    public function createVirtualAccount(
        UserDto $user,
        BVN $bvnData
    ): VirtualAccount;

    public function executeWebhook(Request $request): WebhookResult;

    public function getBanks(): \Illuminate\Support\Collection;

    public function transfer(Account $account, int $amount, $account_no, $bank_code, $account_name, $reference): array;

    public function finalizeTransfer(\App\Models\Withdrawal $withdrawal): void;
}
