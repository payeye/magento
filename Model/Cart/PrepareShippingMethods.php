<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Service\AmountService;
use PayEye\PayEye\Model\Config;

class PrepareShippingMethods
{
    private AmountService $amountService;
    private Config $config;
    private ShipmentEstimationInterface $shipmentEstimation;

    /**
     * @param AmountService $amountService
     * @param Config $config
     * @param ShipmentEstimationInterface $shipmentEstimation
     */
    public function __construct(
        AmountService $amountService,
        Config $config,
        ShipmentEstimationInterface $shipmentEstimation
    ) {
        $this->shipmentEstimation = $shipmentEstimation;
        $this->config = $config;
        $this->amountService = $amountService;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function get(CartInterface $quote): array
    {
        $shippingMethodsData = [];
        $shippingMethods = $this->shipmentEstimation->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());
        $mappedShippingMethods = $this->config->getShippingMethods();
        foreach ($shippingMethods as $shippingMethod) {
            $shippingMethodId = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
            if (array_key_exists($shippingMethodId, $mappedShippingMethods)) {
                $shippingMethodData = [];
                $shippingMethodData['id'] = $shippingMethodId;
                $shippingMethodData['label'] = $shippingMethod->getMethodTitle();
                $shippingMethodData['cost'] = $this->amountService->convertFloatToInteger($shippingMethod->getPriceInclTax());
                $shippingMethodData['regularCost'] = $this->amountService->convertFloatToInteger($shippingMethod->getBaseAmount());
                $shippingMethodData['type'] = $mappedShippingMethods[$shippingMethodId];

                $shippingMethodsData[] = $shippingMethodData;
            }
        }

        return $shippingMethodsData;
    }
}
