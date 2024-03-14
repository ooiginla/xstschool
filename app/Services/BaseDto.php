<?php

namespace App\Services;

class BaseDto {

    protected $requestPayload = [];
    protected $responsePayload;
    protected $code = null;
    protected $message = '';
}