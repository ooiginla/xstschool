<?php

namespace App\Services\Wallet\dto;

class UserDto
{
    public function __construct(
        public $id,
        public $email
    ) {
    }
}
