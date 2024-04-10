<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    public function providerTransaction()
    {
        return $this->belongsTo(ProviderTransaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
