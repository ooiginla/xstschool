<?php

namespace App\ServiceProviders;

class FinalResponseDto {

    public $request_status = false;
    public $response_code = '';
    public $response_message = '';
    public $http_code = 200;
    public $data = [];
    public $provider_raw_data = []; 
    public $debit_business = 'NO';
    public $status = 'FAILED';

    public function __construct($request_status, $response_code, $response_message)
    {
        $this->request_status = $request_status;
        $this->response_code = $response_code;
        $this->response_message = $response_message;
    }
}