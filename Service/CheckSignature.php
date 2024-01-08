<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use PayEye\PayEye\Model\Config;
use PayEye\PayEye\Api\CheckSignatureInterface;

class CheckSignature implements CheckSignatureInterface
{
    private Config $config;
    private GetSignature $getSignature;

    /**
     * @param Config $config
     * @param GetSignature $getSignature
     */
    public function __construct(
        Config $config,
        GetSignature $getSignature
    ) {
        $this->getSignature = $getSignature;
        $this->config = $config;
    }


    /**
     * @param array $request
     * @return bool
     */
    public function check(array $request): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return $this->getSignature->get($request) === $request['signature'];
    }
}
