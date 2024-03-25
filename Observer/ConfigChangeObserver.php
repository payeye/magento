<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayEye\Lib\Auth\AuthConfig;
use PayEye\Lib\Auth\AuthService;
use PayEye\Lib\Auth\HashService;
use PayEye\Lib\Enum\PluginEvents;
use PayEye\Lib\Enum\PluginModes;
use PayEye\Lib\Enum\SignatureFrom;
use PayEye\Lib\Env\Config as PayEyeLibConfig;
use PayEye\Lib\HttpClient\Model\RefreshCartRequest;
use PayEye\Lib\HttpClient\PayEyeHttpClient;
use PayEye\Lib\HttpClient\Model\PluginStatusRequest;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Model\Config;
use Psr\Log\LoggerInterface;

class ConfigChangeObserver implements ObserverInterface
{
    /**
     * @var PayEyeHttpClient
     */
    private $httpClient;
    private $config;
    private $logger;
    private $payeyeQuoteRepository;
    private $productMetadata;

    public function __construct(
        Config $config,
        LoggerInterface $logger,
        PayeyeQuoteRepositoryInterface $payeyeQuoteRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
        $this->payeyeQuoteRepository = $payeyeQuoteRepository;
        $this->logger = $logger;
        $this->config = $config;
        $payeyeLibConfig = PayEyeLibConfig::create($this->config->getApiUrl(), $this->config->getApiDeepLinkUrl());
        $this->httpClient = PayEyeHttpClient::create($payeyeLibConfig, $this->config->getApiVersion());
    }

    public function execute(Observer $observer)
    {
        $shopId = $this->config->getShopId();
        $publicKey = $this->config->getPublicKey();
        $privateKey = $this->config->getPrivateKey();

        $pluginMode = $this->config->isTestMode() ? PluginModes::PLUGIN_MODE_INTEGRATION : PluginModes::PLUGIN_MODE_PRODUCTION;
        $pluginEven = $this->config->isEnabled() ? PluginEvents::PLUGIN_ACTIVATED : PluginEvents::PLUGIN_DEACTIVATED;

        $request = PluginStatusRequest::create(
            $this->config->getApiVersion(),
            $shopId,
            $pluginMode,
            'PHP ' . phpversion(),
            $this->productMetadata->getVersion(),
            $this->config->getPluginVersion(),
            $pluginEven,
            null
        );

        $authConfig = AuthConfig::create($shopId,$publicKey,$privateKey);
        $hashService = HashService::create($authConfig);

        $authService = AuthService::create(
            $hashService,
            SignatureFrom::PLUGIN_UPDATE_STATUS_REQUEST,
            $request->toArray()
        );

        $this->httpClient->sendPluginStatus($request, $authService);
    }
}
