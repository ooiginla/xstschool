<?php

namespace App\ServiceProviders;

interface IServiceProvider
{
    public function processStandardPayload($standardPayload);
    public function mapStandardToAdapterRequest();
    public function mapAdapterResponseToStandard();
    public function makePurchase();
    public function getStatus();
}
   