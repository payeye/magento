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
use PayEye\Lib\Enum\SignatureFrom;
use PayEye\PayEye\Api\GetSignatureInterface;

class Plugin implements PluginInterface
{
    private Config $config;
    private ProductMetadataInterface $productMetadata;
    private CheckSignatureInterface $checkSignature;
    private ErrorResponseInterface $errorResponse;
    private GetSignatureInterface $getSignature;

    public function __construct(
        Config $config,
        ProductMetadataInterface $productMetadata,
        CheckSignatureInterface $checkSignature,
        ErrorResponseInterface $errorResponse,
        GetSignatureInterface $getSignature,
    ) {
        $this->productMetadata = $productMetadata;
        $this->config = $config;
        $this->checkSignature = $checkSignature;
        $this->errorResponse = $errorResponse;
        $this->getSignature = $getSignature;
    }

    /**
     * @param string $signature
     * @return PluginStatusResponseModel
     */
    public function getStatus(string $signature): PluginStatusResponseModel
    {
        $shopId = $this->config->getShopId();

        if (!$this->checkSignature->check([
            'signatureFrom' => SignatureFrom::PLUGIN_STATUS_REQUEST,
            'signature' => $signature,
            'shopIdentifier' => $shopId
        ])) {
            $this->errorResponse->throw(new SignatureNotMatchedException());
        }

        $pluginMode = $this->config->isTestMode() ? 'INTEGRATION' : 'PRODUCTION';
        $pluginEvent = $this->config->isEnabled() ? 'PLUGIN_ACTIVATED' : 'PLUGIN_DEACTIVATED';
        $phpVersion = 'PHP ' . phpversion();

        $response = [
            'apiVersion' => $this->config->getApiVersion(),
            'shopIdentifier' => $shopId,
            'pluginMode' => $pluginMode,
            'languageVersion' => $phpVersion,
            'platformVersion' => $this->productMetadata->getVersion(),
            'pluginVersion' => $this->config->getPluginVersion(),
            'pluginEvent' => $pluginEvent,
            'pluginConfig' => null,
            'signatureFrom' => SignatureFrom::PLUGIN_UPDATE_STATUS_REQUEST
        ];

        $response['signature'] = $this->getSignature->get($response);

        return PluginStatusResponseModel::createFromArray($response);
    }
}
