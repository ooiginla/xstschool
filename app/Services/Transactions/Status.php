<?php

namespace App\Services\Transactions;

interface Status
{
    const PENDING = "PENDING";
    const SUCCESS = "SUCCESS";
    const FAILED = 'FAILED';
    
    const PAID = 'PAID';
    const HELD = 'HELD';
    const RELEASED = 'RELEASED';
}
   