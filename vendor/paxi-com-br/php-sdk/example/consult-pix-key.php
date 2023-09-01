<?php

// Pix - Consult a key

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";
$consultKey = "";

try {
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    $qrcode = $paxi->withPix()->consultKey($consultKey);
    var_dump($qrcode);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}
