<?php

namespace App\ServiceProviders;

interface IServiceProvider
{
    // Main Status
    const TRANSACTION_PENDING = "PENDING";
    const TRANSACTION_SUCCESSFUL = "SUCCESS";
    const TRANSACTION_FAILED = 'FAILED';
    
    // Escrow
    const DEBIT_BUSINESS = 'YES';
    const DO_NOT_DEBIT = 'NO';

    public function processStandardPayload($standardPayload);
    public function mapStandardToAdapterRequest();
    public function mapAdapterResponseToStandard();
    public function makePurchase();
    public function getStatus();
    public function validateParams();

    public function handleSuccessResponse();
    public function handleFailedResponse();
    public function handlePendingResponse();
}
   