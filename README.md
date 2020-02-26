
#Installation
add in file composer.json
```
"require": {
	"megaads/ga-service": "^1.0"
}
```
#
#Get Report From Google Analytic
```
$KEY_FILE_LOCATION =  '../config/client-secrets.json';
$name = 'Chiaki';
$filter = [
    'viewId' => 'xxxxxxxx',
    'from' => '2020-02-01',
    'to' => '2020-02-25',
    'metrics' => [
       'ga:sessions', 'ga:users', 'ga:bounces'
    ]
];
$gaTransfer = new \GaServices\GoogleAnalytic();
$items = $gaTransfer->report($name, $KEY_FILE_LOCATION, $filter);
```

#Google Ads
```
\\ Get cost ads of account
$adsCostAccount = new \GaServices\AdsCostAccount();
$fileConfig = '../config/adsapi_php.ini';
$customerId = 'xxx-xxx-xxxx';
$filter = ['from' => '2020-01-01', 'to' => '2020-01-29']
$cost = $adsAccount->report($customerId, $fileConfig, $filter);
```

