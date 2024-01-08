<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Order;

use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Shipping;
use PayEye\Lib\Order\OrderCreateRequestModel;

class PrepareOrderCreateRequestModel
{
    /**
     * @param string $cartId
     * @param string[] $signatureFrom
     * @param string $signature
     * @param string $cartHash
     * @param string $shippingId
     * @param string $shippingProvider
     * @param Billing $billing
     * @param Shipping $shipping
     * @param bool $hasInvoice
     * @return OrderCreateRequestModel
     */
    public function execute(
        string $cartId,
        array $signatureFrom,
        string $signature,
        string $cartHash,
        string $shippingId,
        string $shippingProvider,
        Billing $billing,
        Shipping $shipping,
        bool $hasInvoice = false
    ): OrderCreateRequestModel {
        $billingArray = null;
        if ($billing) {
            $billingArray = $billing->toArray();
            if ($billing->getAddress()) {
                $billingArray['address'] = $billing->getAddress()->toArray();
            }
        }

        $shippingArray = null;
        if ($shipping) {
            $shippingArray = $shipping->toArray();
            if ($shipping->getAddress()) {
                $shippingArray['address'] = $shipping->getAddress()->toArray();
            }
            if ($shipping->getPickupPoint() && $shipping->getPickupPoint()->toArray()['type']) {
                $shippingArray['pickupPoint'] = $shipping->getPickupPoint()->toArray();
                if ($shipping->getPickupPoint()->getLocation()) {
                    $shippingArray['pickupPoint']['location'] = $shipping->getPickupPoint()->getLocation()->toArray();
                }
            } else {
                $shippingArray['pickupPoint'] = null;
            }
        }

        return OrderCreateRequestModel::createFromArray([
            'cartId' => $cartId,
            'signatureFrom' => $signatureFrom,
            'signature' => $signature,
            'cartHash' => $cartHash,
            'shippingId' => $shippingId,
            'shippingProvider' => $shippingProvider,
            'billing' => $billingArray,
            'shipping' => $shippingArray,
            'hasInvoice' => $hasInvoice
        ]);
    }
}
