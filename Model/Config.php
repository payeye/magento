<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Paypal\Model\AbstractConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache;

class Config extends AbstractConfig
{
    private const XML_PATH_PAYEYE_ENABLED = 'payeye/general/enable';
    private const XML_PATH_PAYEYE_TEST_MODE = 'payeye/general/test_mode';
    private const XML_PATH_PAYEYE_SANDBOX = 'payeye/general/sandbox';
    private const XML_PATH_PAYEYE_SHOP_ID = 'payeye/general/shop_id';
    private const XML_PATH_PAYEYE_PUBLIC_KEY = 'payeye/general/public_key';
    private const XML_PATH_PAYEYE_PRIVATE_KEY = 'payeye/general/private_key';
    private const XML_PATH_PAYEYE_SHIPPING_METHODS = 'payeye/general/shipping_methods';
    private const XML_PATH_PAYEYE_UI_SIDE = 'payeye/ui/side';
    private const XML_PATH_PAYEYE_UI_SIDE_DISTANCE = 'payeye/ui/side_distance';
    private const XML_PATH_PAYEYE_UI_BOTTOM_DISTANCE = 'payeye/ui/bottom_distance';
    private const XML_PATH_PAYEYE_UI_Z_INDEX = 'payeye/ui/z_index';
    private const XML_PATH_PAYEYE_UI_ON_CLICK = 'payeye/ui/on_click';

    private const PLUGIN_VERSION = '1.1.7';

    private const API_VERSION = 2;

    private const API_URL = 'https://prod3a-api.payeye.com/ecommerce-transaction';
    private const API_DEEP_LINK_URL = 'https://payment.payeye.com/order';

    private const API_URL_SANDBOX = 'https://sandbox-api.payeye.com/ecommerce-transaction';

    private ScopeConfigInterface $scopeConfig;
    private Json $jsonSerializer;
    private WriterInterface $configWriter;
    private Cache\Manager $cacheManager;

    private $isTestMode = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer,
        WriterInterface $configWriter,
        Cache\Manager $cacheManager
    )
    {
        $this->jsonSerializer = $jsonSerializer;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;

        parent::__construct($scopeConfig);
    }

    /**
     * @return void
     */
    public function cacheClean()
    {
        $this->cacheManager->clean(['config']);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYEYE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYEYE_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isOnClick(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYEYE_UI_ON_CLICK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getUiSide(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_UI_SIDE,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @return int
     */
    public function getUiSideDistance(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_UI_SIDE_DISTANCE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getUiBottomDistance(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_UI_BOTTOM_DISTANCE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getUiZIndex(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_UI_Z_INDEX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return self::PLUGIN_VERSION;
    }

    /**
     * @return int
     */
    public function getApiVersion(): int
    {
        return self::API_VERSION;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        if ($this->isSandbox()) {
            return self::API_URL_SANDBOX;
        }

        return self::API_URL;
    }

    /**
     * @return string
     */
    public function getApiDeepLinkUrl(): string
    {
        return self::API_DEEP_LINK_URL;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        if ($this->isTestMode === null) {
            $this->isTestMode = $this->scopeConfig->isSetFlag(
                self::XML_PATH_PAYEYE_TEST_MODE,
                ScopeInterface::SCOPE_STORE
            );
        }

        return $this->isTestMode;
    }

    /**
     * @return void
     */
    public function enableTestMode()
    {
        $this->configWriter->save(self::XML_PATH_PAYEYE_TEST_MODE, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->isTestMode = true;
        $this->cacheClean();
    }

    /**
     * @return void
     */
    public function disableTestMode()
    {
        $this->configWriter->save(self::XML_PATH_PAYEYE_TEST_MODE, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->isTestMode = false;
        $this->cacheClean();
    }

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_SHOP_ID,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_PUBLIC_KEY,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_PRIVATE_KEY,
            ScopeInterface::SCOPE_STORE
        ) ?? '';
    }

    /**
     * @return array
     */
    public function getShippingMethods(): array
    {
        $data = [];
        $array = $this->scopeConfig->getValue(
            self::XML_PATH_PAYEYE_SHIPPING_METHODS,
            ScopeInterface::SCOPE_STORE
        );

        if (!$array) {
            return $data;
        }

        foreach ($this->jsonSerializer->unserialize($array) as $item) {
            $data[$item['magento_shipping_method']] = $item['payeye_shipping_method'];
        }

        return $data;
    }
}
