<?php

namespace App\Transformers;

use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ErrorCode;
use Illuminate\Support\Str;

class BaseTransformer {

    public function encrypt($key)
    {
        return $key;
    }

    public function decrypt($key)
    {
        return $key;
    }

    public function generateRandomRef($shortname)
    {
       return $shortname . '_' . time() . '_' . strtoupper(Str::random(5));
    }
}