<?php

namespace App\Services\Responses;

class BusinessResponseDto {

    public $req_status = false;
    public $code = '';
    public $message = '';
    public $http_code = 200;
    public $data = [];
    public $provider_data = []; 
    public $status = 'FAILED';

    public function __construct($req_status, $code, $message)
    {
        $this->req_status = $req_status;
        $this->code = $code;
        $this->message = $message;
    }
}