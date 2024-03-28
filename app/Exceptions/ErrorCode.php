<?php

namespace App\Exceptions;

class ErrorCode
{
    const UNAUTHENTICATED_BUSINESS = "UNAUTHENTICATED_BUSINESS";
    const INVALID_AUTH_KEY = "INVALID_AUTH_KEY";
    const DISABLED_BUSINESS_ACCOUNT = 'DISABLED_BUSINESS_ACCOUNT';
    const GOLIVE_NOT_ENABLED = 'GOLIVE_NOT_ENABLED';
    const NO_PROVIDER_ACTIVE = 'NO_PROVIDER_ACTIVE';
    const REQUEST_PROCESSING = 'REQUEST_PROCESSING';
    const INSUFFICIENT_BALANCE = 'INSUFFICIENT_BALANCE';
    const CONNECTION_TIMEOUT = 'CONNECTION_TIMEOUT';
    const PROVIDER_UNREACHABLE = 'PROVIDER_UNREACHABLE';
    const SUCCESSFUL = 'SUCCESSFUL';



    const CODES = [
        'UNAUTHENTICATED_BUSINESS' => ['message'=> 'Error, unable to identify business', 'code' => 'ER100', 'http_code' => 200],
        'INVALID_AUTH_KEY' => ['message'=> 'Invalid authentication key, check your api key', 'code' => 'ER101', 'http_code' => 200],
        'DISABLED_BUSINESS_ACCOUNT' => ['message'=> 'Your business account is currently disabled, see admin', 'code' => 'ER102', 'http_code' => 200],
        'GOLIVE_NOT_ENABLED' => ['message'=> 'Your business account has not been enabled for go live, see admin', 'code' => 'ER103', 'http_code' => 200],
        'NO_PROVIDER_ACTIVE' => ['message'=> 'No active service provider available at the moment', 'code' => 'ER104', 'http_code' => 200],
        'INSUFFICIENT_BALANCE' => ['message'=> 'Insufficient balance', 'code' => 'ER105', 'http_code' => 200],

        
        'SUCCESSFUL' => ['message'=> 'Requst Successful', 'code' => '00', 'http_code' => 200],

        'REQUEST_PROCESSING' => ['message'=> 'Your request is still processing', 'code' => 'PR100', 'http_code' => 200],
        'CONNECTION_TIMEOUT' => ['message'=> 'Connection timeout while reaching provider', 'code' => 'PR101', 'http_code' => 200],
        'PROVIDER_UNREACHABLE' => ['message'=> 'Unable to reach service provider', 'code' => 'PR102', 'http_code' => 200],
    ];
}
   