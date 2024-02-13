<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use \Magento\Framework\App\ProductMetadataInterface;
use PayEye\Lib\Exception\SignatureNotMatchedException;
use PayEye\Lib\Plugin\PluginStatusResponseModel;
use PayEye\PayEye\Api\PluginInterface;
use PayEye\PayEye\Api\CheckSignatureInterface;
use PayEye\PayEye\Api\ErrorResponseInterface;

class Plugin implements PluginInterface
{
    private Config $config;
    private ProductMetadataInterface $productMetadata;
    private CheckSignatureInterface $checkSignature;
    private ErrorResponseInterface $errorResponse;

    public function __construct(
        Config $config,
        ProductMetadataInterface $productMetadata,
        CheckSignatureInterface $checkSignature,
        ErrorResponseInterface $errorResponse
    ) {
        $this->productMetadata = $productMetadata;
        $this->config = $config;
        $this->checkSignature = $checkSignature;
        $this->errorResponse = $errorResponse;
    }

    /**
     * @param string $signature
     * @return PluginStatusResponseModel
     */
    public function getStatus(string $signature): PluginStatusResponseModel
    {
        if (!$this->checkSignature->check(['signatureFrom' => [], 'signature' => $signature])) {
            $this->errorResponse->throw(new SignatureNotMatchedException());
        }

        $shopId = $this->config->getShopId();

        $pluginMode = $this->config->isTestMode() ? 'INTEGRATION' : 'PRODUCTION';
        $pluginEven = $this->config->isEnabled() ? 'PLUGIN_ACTIVATED' : 'PLUGIN_DEACTIVATED';
        $phpVersion = 'PHP ' . phpversion();

        $pluginStatus = PluginStatusResponseModel::create(
            $this->config->getApiVersion(),
            $shopId,
            $pluginMode,
            $phpVersion,
            $this->productMetadata->getVersion(),
            $this->config->getPluginVersion(),
            $pluginEven,
            []
        );

        return $pluginStatus;
    }
}
