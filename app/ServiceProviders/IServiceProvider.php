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
    public function mapAdapterResponseToStandard($response);
    public function purchase();
    public function get_status();
    public function validateParams();
    public function handleSuccessResponse($response);
    public function handleFailedResponse($response);
    public function handlePendingResponse($response);
    public function determineTransactionStatus($response);
}
   