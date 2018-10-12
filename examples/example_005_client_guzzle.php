<?php

use GuzzleHttp\Client;
use LogicalSteps\Async\Async;

require __DIR__ . '/../vendor/autoload.php';

function trace($error, $response)
{
    if ($error) {
        echo (string)$error . PHP_EOL;
        return;
    }
    echo json_encode($response) . PHP_EOL;
}


function status($url)
{
    $client = new Client(['http_errors' => false]);
    $response = yield $client->getAsync($url);
    return $response->getStatusCode();
}

$async = new Async();
$async->execute(status('http://httpbin.org/get'), 'trace');
$async->execute(status('http://httpbin.org/missingPage'), 'trace');
