<?php

namespace App\Services\Transactions;

interface Action
{
    // Main Status
    const PURCHASE = "PURCHASE";
    const GET_STATUS = "GET_STATUS";
    const GET_BALANCE = 'GET_BALANCE';
}
   