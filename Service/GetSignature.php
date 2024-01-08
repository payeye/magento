<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use PayEye\Lib\Auth\AuthConfig;
use PayEye\Lib\Auth\AuthService;
use PayEye\Lib\Auth\HashService;
use PayEye\PayEye\Model\Config;
use PayEye\PayEye\Api\GetSignatureInterface;

class GetSignature implements GetSignatureInterface
{
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $request
     * @return string
     */
    public function get(array $request): string
    {
        if (!$this->config->isEnabled()) {
            return '';
        }

        $shopId = $this->config->getShopId();
        $publicKey = $this->config->getPublicKey();
        $privateKey = $this->config->getPrivateKey();

        $authConfig = AuthConfig::create($shopId,$publicKey,$privateKey);
        $hashService = HashService::create($authConfig);

        $authService = AuthService::create($hashService,$request['signatureFrom'],$request);

        return $authService->getSignature();
    }
}
