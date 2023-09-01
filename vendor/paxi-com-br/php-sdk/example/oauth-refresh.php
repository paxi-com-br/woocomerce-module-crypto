<?php

// Oauth / Refresh

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";

try {
    echo "oauth: ";
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    var_dump($paxi);

    echo "refresh: ";
    $paxi->refreshToken();
    var_dump($paxi);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}
