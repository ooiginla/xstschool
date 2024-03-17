<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'id',
        'business_id'
    ];

    public function getAccountType()
    {
        return $this->account_type == 1 ? 'REGULAR' : 'GL';
    }

    // make the account_no field the route key
    public function getRouteKeyName()
    {
        return 'account_no';
    }

    // customize error message for route model binding
    public function resolveRouteBinding($value, $field = null)
    {
        $account = $this->where('account_no', $value)->first();
        if (!$account) {
            throw new \Exception('Account not found');
        }
        return $account;
    }

    public function transactions()
    {
        return $this->hasMany(History::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
