<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use \Magento\Framework\App\ProductMetadataInterface;
use PayEye\Lib\Enum\PluginEvents;
use PayEye\Lib\Enum\PluginModes;
use PayEye\Lib\Exception\SignatureNotMatchedException;
use PayEye\Lib\Plugin\PluginStatusRequestModel;
use PayEye\Lib\Plugin\PluginStatusResponseModel;
use PayEye\PayEye\Api\PluginInterface;
use PayEye\PayEye\Api\CheckSignatureInterface;
use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\Lib\Enum\SignatureFrom;
use PayEye\PayEye\Api\GetSignatureInterface;
use PayEye\PayEye\Exception\ModuleDisabledException;
use PayEye\PayEye\Model\Plugin\PreparePluginStatusRequestModel;

class Plugin implements PluginInterface
{
    private Config $config;
    private ProductMetadataInterface $productMetadata;
    private CheckSignatureInterface $checkSignature;
    private ErrorResponseInterface $errorResponse;
    private GetSignatureInterface $getSignature;
    private PreparePluginStatusRequestModel $preparePluginStatusRequestModel;

    public function __construct(
        Config $config,
        ProductMetadataInterface $productMetadata,
        CheckSignatureInterface $checkSignature,
        ErrorResponseInterface $errorResponse,
        GetSignatureInterface $getSignature,
        PreparePluginStatusRequestModel $preparePluginStatusRequestModel
    ) {
        $this->productMetadata = $productMetadata;
        $this->config = $config;
        $this->checkSignature = $checkSignature;
        $this->errorResponse = $errorResponse;
        $this->getSignature = $getSignature;
        $this->preparePluginStatusRequestModel = $preparePluginStatusRequestModel;
    }

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
    ): PluginStatusResponseModel
    {
        $request = $this->preparePluginStatusRequestModel->execute(
            $shopIdentifier,
            $pluginEvent,
            $pluginMode,
            $signatureFrom,
            $signature
        );

        $this->checkIfCanProcess($request->toArray());

        if ($request->getPluginMode() === PluginModes::PLUGIN_MODE_INTEGRATION) {
            $this->config->enableTestMode();
        }

        if ($request->getPluginMode() === PluginModes::PLUGIN_MODE_PRODUCTION) {
            $this->config->disableTestMode();
        }

        return $this->prepareResponse($shopIdentifier, PluginEvents::PLUGIN_CONFIG_CHANGED);
    }

    /**
     * @param array $request
     * @return void
     */
    private function checkIfCanProcess(array $request): void
    {
        if (!$this->config->isEnabled()) {
            $this->errorResponse->throw(new ModuleDisabledException());
        }

        if (!$this->checkSignature->check($request)) {
            $this->errorResponse->throw(new SignatureNotMatchedException());
        }
    }

    /**
     * @param string $signature
     * @return PluginStatusResponseModel
     */
    public function getStatus(string $signature): PluginStatusResponseModel
    {
        $shopId = $this->config->getShopId();

        $this->checkIfCanProcess([
            'signatureFrom' => SignatureFrom::PLUGIN_STATUS_REQUEST,
            'signature' => $signature,
            'shopIdentifier' => $shopId
        ]);

        return $this->prepareResponse($shopId, PluginEvents::PLUGIN_INFO);
    }

    /**
     * @return PluginStatusResponseModel
     */
    protected function prepareResponse($shopId, $pluginEvent)
    {
        $pluginMode = $this->config->isTestMode() ? PluginModes::PLUGIN_MODE_INTEGRATION : PluginModes::PLUGIN_MODE_PRODUCTION;
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
