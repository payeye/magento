<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Plugin;

use PayEye\Lib\Plugin\PluginStatusRequestModel;

class PreparePluginStatusRequestModel
{
    /**
     * @param string $shopIdentifier
     * @param string $pluginEvent
     * @param string $pluginMode
     * @param array $signatureFrom
     * @param string $signature
     * @return PluginStatusRequestModel
     */
    public function execute(
        string $shopIdentifier,
        string $pluginEvent,
        string $pluginMode,
        array $signatureFrom,
        string $signature
    ): PluginStatusRequestModel {
        return PluginStatusRequestModel::createFromArray([
            'shopIdentifier' => $shopIdentifier,
            'pluginEvent' => $pluginEvent,
            'pluginMode' => $pluginMode,
            'signatureFrom' => $signatureFrom,
            'signature' => $signature,
        ]);
    }
}
