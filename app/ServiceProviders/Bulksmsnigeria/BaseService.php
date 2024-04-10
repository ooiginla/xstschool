<?php

namespace App\ServiceProviders\BulkSmsNigeria;

class BaseService {

    private $base_url = '';

    public function setBaseUrl($url){
        $this->base_url = $url;
    }

    public function getBaseUrl() {
        return $this->base_url;
    }
}