<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;

class PrepareQuoteData
{
    private StoreManagerInterface $storeManager;
    private PrepareProductsData $prepareProductsData;
    private PrepareTotalsData $prepareTotalsData;
    private PreparePromoCodes $preparePromoCodes;
    private PrepareCartType $prepareCartType;
    private PrepareShippingMethods $prepareShippingMethods;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PrepareProductsData $prepareProductsData
     * @param PrepareTotalsData $prepareTotalsData
     * @param PreparePromoCodes $preparePromoCodes
     * @param PrepareCartType $prepareCartType
     * @param PrepareShippingMethods $prepareShippingMethods
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PrepareProductsData $prepareProductsData,
        PrepareTotalsData $prepareTotalsData,
        PreparePromoCodes $preparePromoCodes,
        PrepareCartType $prepareCartType,
        PrepareShippingMethods $prepareShippingMethods
    ) {
        $this->prepareShippingMethods = $prepareShippingMethods;
        $this->preparePromoCodes = $preparePromoCodes;
        $this->prepareCartType = $prepareCartType;
        $this->prepareTotalsData = $prepareTotalsData;
        $this->prepareProductsData = $prepareProductsData;
        $this->storeManager = $storeManager;
    }

    /**
     * @param CartInterface $quote
     * @param bool $shippingAddressSended
     * @return array
     */
    public function get(CartInterface $quote, bool $shippingAddressSended = false): array
    {
        $quoteData['shop']['name'] = $this->storeManager->getStore()->getName();
        $quoteData['shop']['url'] = $this->storeManager->getStore()->getBaseUrl();
        $quoteData['products'] = $this->prepareProductsData->get($quote);
        $quoteData['cart'] = $this->prepareTotalsData->get($quote);
        $quoteData['shippingId'] = $quote->getShippingAddress()->getShippingMethod() ?: null;
        $quoteData['promoCodes'] = $this->preparePromoCodes->get($quote);
        $quoteData['cartType'] = $this->prepareCartType->get($quote);
        $quoteData['currency'] = $quote->getCurrency()->getStoreCurrencyCode() ?:
            $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $quoteData['shippingMethods'] = $shippingAddressSended ? $this->prepareShippingMethods->get($quote) : [];

        return $quoteData;
    }
}
