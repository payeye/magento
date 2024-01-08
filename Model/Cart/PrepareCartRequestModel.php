<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use PayEye\Lib\Cart\CartRequestModel;
use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Shipping;

class PrepareCartRequestModel
{
    /**
     * @param string $cartId
     * @param array $signatureFrom
     * @param string $signature
     * @param string|null $shippingId
     * @param string|null $shippingProvider
     * @param Billing|null $billing
     * @param Shipping|null $shipping
     * @return CartRequestModel
     */
    public function execute(
        string $cartId,
        array $signatureFrom,
        string $signature,
        ?string $shippingId,
        ?string $shippingProvider,
        ?Billing $billing,
        ?Shipping $shipping
    ): CartRequestModel {
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

        return CartRequestModel::createFromArray([
            'cartId' => $cartId,
            'signatureFrom' => $signatureFrom,
            'shippingId' => $shippingId,
            'shippingProvider' => $shippingProvider,
            'billing' => $billingArray,
            'shipping' => $shippingArray,
            'signature' => $signature
        ]);
    }
}
