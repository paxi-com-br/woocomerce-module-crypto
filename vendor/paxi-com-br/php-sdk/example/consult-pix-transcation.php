<?php

// Pix - Consult a transcation

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";
$transactionId = "";

try {
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    $qrcode = $paxi->withPix()->consultTransaction($transactionId);
    var_dump($qrcode);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}
