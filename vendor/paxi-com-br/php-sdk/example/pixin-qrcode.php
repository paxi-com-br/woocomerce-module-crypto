<?php

// PixIn - QRCode

include __DIR__ . '/../vendor/autoload.php';

$apiKey = "";
$apiSecret = "";
$depositKey = "";
$depositAmount = 1.50;

try {
    $paxi = new PAXI\SDK\PAXI($apiKey, $apiSecret);
    $qrcode = $paxi->withPix()->generateQRCode($depositKey, $depositAmount);
    var_dump($qrcode);
} catch (\Exception $err) {
    echo "Error: {$err->getMessage()}";
}
