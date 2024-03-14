<?php

namespace App\Exceptions;

class ErrorCode
{
    const UNAUTHENTICATED_BUSINESS = "UNAUTHENTICATED_BUSINESS";
    const INVALID_AUTH_KEY = "INVALID_AUTH_KEY";
    const DISABLED_BUSINESS_ACCOUNT = 'DISABLED_BUSINESS_ACCOUNT';
    const GOLIVE_NOT_ENABLED = 'GOLIVE_NOT_ENABLED';
    const REQUEST_PROCESSING = 'REQUEST_PROCESSING';
    const SUCCESSFUL = 'SUCCESSFUL';



    const CODES = [
        'UNAUTHENTICATED_BUSINESS' => ['message'=> 'Error, unable to identify business', 'code' => 'ER100', 'http_code' => 401],
        'INVALID_AUTH_KEY' => ['message'=> 'Invalid authentication key, check your api key', 'code' => 'ER101', 'http_code' => 401],
        'DISABLED_BUSINESS_ACCOUNT' => ['message'=> 'Your business account is currently disabled, see admin', 'code' => 'ER102', 'http_code' => 401],
        'GOLIVE_NOT_ENABLED' => ['message'=> 'Your business account has not been enabled for go live, see admin', 'code' => 'ER103', 'http_code' => 401],
        
        'SUCCESSFUL' => ['message'=> 'Successful', 'code' => '00', 'http_code' => 200],
        'REQUEST_PROCESSING' => ['message'=> 'Your request is still processing', 'code' => 'PR100', 'http_code' => 200],
    ];
}
   