<?php

namespace App\ServiceProviders\Africastalking;

use App\Models\BusinessIntegration;

trait Africastalking {

    protected $provider_code = 'Africastalking';
    protected $base_url = 'https://api.africastalking.com/version1';
    protected $username = 'itservices';

    public function setCredentials(){
       $this->credentials = BusinessIntegration::where('id', $this->standardPayload['integration_id'])->first();
    }

    public function getProvider(){
       return  Provider::where('code', $this->provider_code)->first();
    }

    public function getBusiness(){
        return Businss::where('username',$this->standardPayload['sa_business'])->first();
    }

    public function getApiKey() {
        return $this->credentials->public_api_key;
    }

    public function getUsername() {
       return $this->username;
    }
 }