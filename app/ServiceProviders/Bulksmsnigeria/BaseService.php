<?php

namespace App\ServiceProviders\BulkSmsNigeria;

use App\Models\ProviderTransaction;
use Illuminate\Support\Facades\Http;
use App\ServiceProviders\BaseServiceProvider;

class BaseService extends BaseServiceProvider {

    protected $base_url = 'https://www.bulksmsnigeria.com/api/v1';
    protected $api_key = 'IL6sNq0fqb0jtj6vlvcMr5pT8VZavLDCzY74p89UWdE13dJ5COJndPt86LvV';
}