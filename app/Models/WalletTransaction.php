<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\Wallet\WalletResponse;


class WalletTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function updateData($status, $message, $total_debit)
    {
        $this->message = $message;
        $this->status = $status;
        $this->total_debit = $total_debit;
        $this->save();

        return $this;
    }

    public function getWalletResponse(): WalletResponse
    {
        return new WalletResponse($this->status, $this->message, $this->reference, $this->total_debit);
    }

    public function isFinalized()
    {
        // PENDING
        return ($this->status != 1) ? true : false;
    }

}
