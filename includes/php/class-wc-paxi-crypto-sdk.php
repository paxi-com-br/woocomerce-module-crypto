<?php

if (!defined("ABSPATH")) {
    exit;
}

use PAXI\SDK\PAXI as PAXI_API_SDK;

class WC_PAXI_Crypto_SDK
{
    public function process_payment($paxi_woocommerce, $order)
    {
        $apiKey = $paxi_woocommerce->api_key;
        $apiSecret = $paxi_woocommerce->api_secret;
        $depositAmount = $order->get_total();

        try {
            $paxi = new PAXI_API_SDK($apiKey, $apiSecret);
            return $paxi->withCrypto()->generateURL($depositAmount);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function process_webhook($paxi_woocommerce)
    {
        $webhookSecret = $paxi_woocommerce->webhook_secret;

        try {
            $paxi = new PAXI_API_SDK("", "", false);
            $payload = $paxi->handleWebhook($webhookSecret);

            if (
                $payload["event"] == "transaction.CryptoIn"
                && $order = $paxi_woocommerce->order_by_crypto_id($payload["id"])
            ) {
                $this->event_cryptoin($order, $payload);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function event_cryptoin($order, $payload)
    {
        if ($payload["status"] == "CONCLUDED" && $payload["amount_brl"] >= $order->get_total()) {
            $order->update_meta_data("paxi_crypto_blockchain", $payload["blockchain"]);
            $order->update_meta_data("paxi_crypto_txid", $payload["txid"]);
            $order->update_meta_data("paxi_crypto_coin", $payload["coin"]);
            $order->update_status("processing");
            $order->save();
        } else if ($payload["status"] == "CANCELED") {
            $order->update_status("cancelled");
            $order->save();
        }
    }
}
