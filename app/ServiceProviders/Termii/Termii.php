<?php

namespace App\ServiceProviders\Termii;


trait Termii {

    protected $provider_code = 'Termii';
    protected $base_url = 'https://api.ng.termii.com/api';
    protected $provider_error_codes = [];
 }