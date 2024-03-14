<?php

namespace App\Services\Transactions;

interface Status
{
    const PENDING = "PENDING";
    const SUCCESS = "SUCCESS";
    const FAILED = 'FAILED';
}
   