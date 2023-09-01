<?php

// PixOut - Transfer to key

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";
$transferKey = "";
$transferAmount = 1.50;

try {
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    $qrcode = $paxi->withPix()->transferTo($transferKey, $transferAmount);
    var_dump($qrcode);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}

