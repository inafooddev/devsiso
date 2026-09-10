<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://jobs.asiatop.co.id:9080/trx/export?block=SLSINA';
$token = 'em9WOU9KVjNVbEhBM1V6UlVVTUZxTTNvSEwzeHUxOGxKQlJyemtkbXxIT0lOQQ==';

$client = new \GuzzleHttp\Client(['verify' => false]);
$response = $client->get($url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $token
    ]
]);
$data = json_decode($response->getBody(), true);

echo "\n--- API KEYS ---\n";
if (isset($data['data']) && count($data['data']) > 0) {
    print_r(array_keys($data['data'][0]));
    echo "\n--- SAMPLE DATA ---\n";
    print_r($data['data'][0]);
} else if (is_array($data) && count($data) > 0) {
    if (isset($data[0])) {
        print_r(array_keys($data[0]));
    } else {
        print_r(array_keys($data));
    }
} else {
    echo "No data found.\n";
}

echo "\n--- DB COLUMNS ---\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('fsalesman');
print_r($columns);
