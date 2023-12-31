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

class Config extends AbstractConfig
{
    private const XML_PATH_PAYEYE_ENABLED = 'payeye/general/enable';
    private const XML_PATH_PAYEYE_TEST_MODE = 'payeye/general/test_mode';
    private const XML_PATH_PAYEYE_SHOP_ID = 'payeye/general/shop_id';
    private const XML_PATH_PAYEYE_PUBLIC_KEY = 'payeye/general/public_key';
    private const XML_PATH_PAYEYE_PRIVATE_KEY = 'payeye/general/private_key';
    private const XML_PATH_PAYEYE_SHIPPING_METHODS = 'payeye/general/shipping_methods';
    private const API_DEEP_LINK_URL = 'https://payment.payeye.com/order';
    private const TEST_API_DEEP_LINK_URL = 'https://dev-payment.payeye.com/order';

    private const API_URL = 'https://prod3a-api.payeye.com/ecommerce-transaction';
    private const TEST_API_URL = 'https://uat3a-api.payeye.com/ecommerce-transaction';
    private ScopeConfigInterface $scopeConfig;
    private Json $jsonSerializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($scopeConfig);
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
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->isTestMode() ? self::TEST_API_URL : self::API_URL;
    }

    /**
     * @return string
     */
    public function getApiDeepLinkUrl(): string
    {
        return $this->isTestMode() ? self::TEST_API_DEEP_LINK_URL : self::API_DEEP_LINK_URL;
    }


    /**
     * @return bool
     */
    private function isTestMode(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYEYE_TEST_MODE,
            ScopeInterface::SCOPE_STORE
        );
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
