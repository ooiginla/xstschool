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
    const INVALID_PAYLOAD = 'INVALID_PAYLOAD';

   
    const PROVIDER_INVALID_AUTH = 'PROVIDER_INVALID_AUTH';
    const PROVIDER_OUT_OF_FUNDS = 'PROVIDER_OUT_OF_FUNDS';
    const PROVIDER_ERROR = 'PROVIDER_ERROR';
    const PROVIDER_UNKNOWN_RESPONSE = 'PROVIDER_UNKNOWN_RESPONSE';
    const PROVIDER_INVALID_PAYLOAD = 'PROVIDER_INVALID_PAYLOAD';
    const UNABLE_TO_RETRY = 'UNABLE_TO_RETRY';
    const UNABLE_TO_RETRY_STATUS_ISSUES = 'UNABLE_TO_RETRY_STATUS_ISSUES';
    const TRANSACTION_NOT_FOUND = 'TRANSACTION_NOT_FOUND';



    const CODES = [
        'SUCCESSFUL' =>                 ['code' => '00', 'message'=> 'Requst Successful', 'http_code' => 200],

        'REQUEST_PROCESSING' =>         ['code' => 'PR100', 'message'=> 'Your request is still processing', 'http_code' => 200],
        'CONNECTION_TIMEOUT' =>         ['code' => 'PR101', 'message'=> 'Connection timeout while reaching provider','http_code' => 200],
        'PROVIDER_UNREACHABLE' =>       ['code' => 'PR102', 'message'=> 'Unable to reach service provider','http_code' => 200],
        
        'UNAUTHENTICATED_BUSINESS' =>   ['code' => 'ER100', 'message'=> 'Error, unable to identify business', 'http_code' => 200],
        'INVALID_AUTH_KEY' =>           ['code' => 'ER101', 'message'=> 'Invalid authentication key, check your api key', 'http_code' => 200],
        'DISABLED_BUSINESS_ACCOUNT' =>  ['code' => 'ER102', 'message'=> 'Your business account is currently disabled, see admin', 'http_code' => 200],
        'GOLIVE_NOT_ENABLED' =>         ['code' => 'ER103', 'message'=> 'Your business account has not been enabled for go live, see admin', 'http_code' => 200],
        'NO_PROVIDER_ACTIVE' =>         ['code' => 'ER104', 'message'=> 'No active service provider available at the moment', 'http_code' => 200],
        'INSUFFICIENT_BALANCE' =>       ['code' => 'ER105', 'message'=> 'Insufficient balance', 'http_code' => 200],
        'INVALID_PAYLOAD' =>            ['code' => 'ER106', 'message'=> 'Invalid Payload', 'http_code' => 200],
        'UNABLE_TO_RETRY' =>            ['code' => 'ER112', 'message'=> 'Unable to retry, transaction archived', 'http_code' => 200],
        'UNABLE_TO_RETRY_STATUS_ISSUES' => ['code' => 'ER113', 'message'=> 'Unable to retry, transaction is not in a retry state, call get status', 'http_code' => 200],
        'TRANSACTION_NOT_FOUND' =>      ['code' => 'ER114', 'message'=> 'Transaction not found', 'http_code' => 200],

        'PROVIDER_INVALID_AUTH' =>      ['code' => 'ER107', 'message'=> 'Invalid auth on provider', 'http_code' => 200],
        'PROVIDER_OUT_OF_FUNDS' =>      ['code' => 'ER108', 'message'=> 'Out of funds on provider', 'http_code' => 200],
        'PROVIDER_ERROR' =>             ['code' => 'ER109', 'message'=> 'Error on provider', 'http_code' => 200],
        'PROVIDER_UNKNOWN_RESPONSE' =>  ['code' => 'ER110', 'message'=> 'Provider sent an unknown response', 'http_code' => 200],
        'PROVIDER_INVALID_PAYLOAD' =>   ['code' => 'ER111', 'message'=> 'Invalid payload sent to provider', 'http_code' => 200],

    ];
}
   