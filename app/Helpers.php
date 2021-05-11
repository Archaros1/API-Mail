<?php

namespace App;

use GuzzleHttp\Client;

if (!\function_exists('getClient')) {
    function getClient(string $uri): Client
    {
        return new Client([
            // Base URI is used with relative requests
            'base_uri' => $uri,
            // You can set any number of default request options.
            // 'timeout'  => 5.0,
            'verify' => false,
        ]);
    }
}
