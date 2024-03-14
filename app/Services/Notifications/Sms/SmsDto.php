<?php

namespace App\Services;

class SmsDto extends BaseDto{

    public $prefix;
    public $phonenumber;
    public $message;
}