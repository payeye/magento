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

    /**
     * @param string $shopIdentifier
     * @param string $pluginEvent
     * @param string $pluginMode
     * @param string[] $signatureFrom
     * @param string $signature
     * @return PluginStatusResponseModel
     */
    public function setStatus(
        string $shopIdentifier,
        string $pluginEvent,
        string $pluginMode,
        array $signatureFrom,
        string $signature
    ): PluginStatusResponseModel;
}
