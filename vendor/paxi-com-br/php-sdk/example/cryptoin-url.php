<?php

// CryptoIn - URL

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";
$depositAmount = 5.0; // BRL
$depositExpiration = "30M"; // 30 minutes

try {
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    $url = $paxi->withCrypto()->generateURL($depositAmount, $depositExpiration);
    var_dump($url);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}
