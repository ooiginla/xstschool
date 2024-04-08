<?php

namespace App\Services\Transactions;

interface Status
{
    // Main Status
    const PENDING = "PENDING";
    const SUCCESS = "SUCCESS";
    const FAILED = 'FAILED';
    
    // Escrow
    const PAID = 'PAID';
    const HELD = 'HELD';
    const RELEASED = 'RELEASED';
}
   