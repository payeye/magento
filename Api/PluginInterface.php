<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\Lib\Plugin\PluginStatusResponseModel;

interface PluginInterface
{
    /**
     * @param string $signature
     * @return PluginStatusResponseModel
     */
    public function getStatus(string $signature): PluginStatusResponseModel;
}
