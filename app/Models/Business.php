<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    static function Oystr()
    {
        return Business::find(config('app.oystr_id'));
    }

    public function documents()
    {
        return $this->hasMany(BusinessDocument::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function providerTransactions()
    {
        return $this->hasMany(ProviderTransaction::class);
    }

}