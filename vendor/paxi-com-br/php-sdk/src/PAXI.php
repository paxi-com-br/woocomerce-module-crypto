<?php

namespace PAXI\SDK;

use PAXI\SDK\Gateway\CryptoGateway;
use PAXI\SDK\Gateway\PIXGateway;
use PAXI\SDK\Support\HMACSupport;

class PAXI
{
    /**
     * @var HTTPClient
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param bool $automaticOauth
     */
    public function __construct($apiKey, $apiSecret, $automaticOauth = true)
    {
        $this->client = new HTTPClient();
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if ($automaticOauth) {
            $this->oauthToken();
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function oauthToken()
    {
        $res = $this->client->request("/oauth/token", "POST", [
            "grant_type" => "client_credentials",
            "api_key" => $this->apiKey,
            "api_secret" => $this->apiSecret
        ]);

        if (!isset($res["success"]) || $res["success"] !== true) {
            throw new \Exception($res["message"] ?? "Access denied");
        }


        $this->client->setAuthorization($res["access_token"], "Bearer");
        $this->accessToken = $res["access_token"];
        $this->refreshToken = $res["refresh_token"];
        return true;
    }

    /**
     * @return bool
     */
    public function refreshToken()
    {
        $res = $this->client->request("/oauth/token", "POST", [
            "grant_type" => "refresh_token",
            "refresh_token" => $this->refreshToken
        ]);

        if (!isset($res["success"]) || $res["success"] !== true) {
            throw new \Exception($res["message"] ?? "Access denied");
        }

        $this->accessToken = $res["access_token"];
        $this->refreshToken = $res["refresh_token"];
        return true;
    }

    /**
     * @param string $webhookSecret
     * @return bool
     */
    public function handleWebhook($webhookSecret)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(400); // 400 - Bad Request
            exit;
        }

        $receivedHmac = HMACSupport::getFromHTTPHeader();
        if (!$receivedHmac) {
            http_response_code(403); // 403 - Forbidden
            exit;
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);
        $raw = json_encode($payload); // fix: payload in raw

        if (!HMACSupport::verifyHash($receivedHmac, $raw, $webhookSecret)) {
            http_response_code(401); // 401 - Unauthorized
            exit;
        }

        return $payload;
    }

    /**
     * @return HTTPClient
     */
    public function withApi()
    {
        return $this->client;
    }

    /**
     * @return PIXGateway
     */
    public function withPix()
    {
        return new PIXGateway($this);
    }

    /**
     * @return CryptoGateway
     */
    public function withCrypto()
    {
        return new CryptoGateway($this);
    }
}
