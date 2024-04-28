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

    public function generateRandomString($length = 10, $use_num = true, $use_lowerchar = false, $use_upperchar = false, $use_symbols = false) {
        $characters = '';
        $numbers = '0123456789';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols = "@$#&*{}[],=-().+;'/|\~:%^!'";
        
        if($use_num){
            $characters .= $numbers;
        }

        if($use_lowerchar){
            $characters .= $lower;
        }

        if($use_upperchar){
            $characters .= $upper;
        }

        if($use_symbols){
            $characters .= $symbols;
        }
    
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
}