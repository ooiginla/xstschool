
<?php

return [

    'Bulksmsnigeria' => [
        'url' => env('BULKSMSNIGERIA_URL', 'https://www.bulksmsnigeria.com/api/v1'),
        'username' => env('BULKSMSNIGERIA_USERNAME', 'serviceadapter'),
        'api_key' => env('BULKSMSNIGERIA_API_KEY', 'IL6sNq0fqb0jtj6vlvcMr5pT8VZavLDCzY74p89UWdE13dJ5COJndPt86LvV'),
    ],

    'Africaistalking' => [
        'url' => env('AFRICAISTALKING_URL', 'https://api.africastalking.com/version1'),
        'username' => env('AFRICAISTALKING_USERNAME', 'sandbox'),
        'app_name' => env('AFRICAISTALKING_APPNAME', 'Sandbox'),
        'api_key' => env('AFRICAISTALKING_API_KEY', 'e29ce929496f1adf2785b7b068dca39b6ce7c5a39b48cd981456d8a52ffec13b'),
    ],
];


/*
{
    "message": "Invalid Sender id"
} - 400

{
    "msg": "",
    "status": -2
} - 200

AFT
{
"SMSMessageData": {
"Message": "Sent to 0/1 Total Cost: 0",
"Recipients": [
{
"cost": "0",
"messageId": "None",
"number": "+2347033134187",
"status": "DoNotDisturbRejection",
"statusCode": 409
}
]
}
}
*/