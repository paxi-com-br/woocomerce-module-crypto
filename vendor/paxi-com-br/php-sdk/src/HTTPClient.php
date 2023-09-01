<?php

namespace PAXI\SDK;

class HTTPClient
{
    /** @var string */
    private $baseUrl = "https://paxi.com.br/api/v1";

    /** @var string */
    private $accessToken;

    /** @var string */
    private $tokenType;

    /**
     * @param string $token
     * @param string $type
     * @return void
     */
    public function setAuthorization($token, $type = "Bearer")
    {
        $this->accessToken = $token;
        $this->tokenType = $type;
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array $data
     * @return 
     */
    public function request($uri, $method, $data = [], $headers = [])
    {
        if ($this->accessToken != "") {
            $headers[] = "Authorization: {$this->tokenType} {$this->accessToken}";
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array_merge([
                "Accept: application/json",
                "Content-Type: application/json"
            ], $headers)
        ]);

        if ($method == "POST") {
            $body = json_encode($data, JSON_PRETTY_PRINT);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new \Exception(curl_error($curl), $code);
        }

        $json = json_decode($response, true);

        if (is_null($json)) {
            throw new \Exception(json_last_error_msg(), $code);
        }

        return $json;
    }
}
