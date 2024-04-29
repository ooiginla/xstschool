<?php

namespace App\ServiceProviders\Multitexter;


trait Multitexter {

    protected $provider_code = 'Multitexter';
    protected $base_url = 'https://app.multitexter.com/v2/app';

    protected $provider_error_codes = [
        1 => 'Message sent successful',
        2 => 'Invalid Parameter',
        3 => 'Account suspended due to fraudulent message',
        4 => 'Invalid Display name',
        5 => 'Invalid Message content',
        6 => 'Invalid recipient',
        7 => 'Insufficient unit',
        10 => 'Unknown error',
        401 => 'Unauthenticated',
    ];
 }