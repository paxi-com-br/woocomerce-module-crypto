<?php

namespace PAXI\SDK\Gateway;

use PAXI\SDK\PAXI;
use PAXI\SDK\HTTPClient;

abstract class BaseGateway
{
    /**
     * @var PAXI
     */
    protected $paxi;

    /**
     * @var HTTPClient
     */
    protected $httpClient;

    /**
     * @param PAXI $paxi
     */
    public function __construct(PAXI $paxi)
    {
        $this->paxi = $paxi;
        $this->httpClient = $paxi->withApi();
    }
}
