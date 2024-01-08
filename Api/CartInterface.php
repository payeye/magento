<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Shipping;
use PayEye\Lib\PromoCode\PromoCodeResponseModel;
use PayEye\Lib\Cart\CartResponseModel;

interface CartInterface
{
    /**
     * @param string $cartId
     * @param string[] $signatureFrom
     * @param string $signature
     * @param string|null $shippingId
     * @param string|null $shippingProvider
     * @param \PayEye\Lib\Model\Billing|null $billing
     * @param \PayEye\Lib\Model\Shipping|null $shipping
     * @return \PayEye\Lib\Cart\CartResponseModel
     */
    public function get(
        string $cartId,
        array $signatureFrom,
        string $signature,
        string $shippingId = null,
        string $shippingProvider = null,
        Billing $billing = null,
        Shipping $shipping = null
    ): CartResponseModel;


    /**
     * @param string $cartId
     * @param string $promoCode
     * @param string[] $signatureFrom
     * @param string $signature
     * @return \PayEye\Lib\PromoCode\PromoCodeResponseModel
     */
    public function setPromoCode(
        string $cartId,
        string $promoCode,
        array $signatureFrom,
        string $signature
    ): PromoCodeResponseModel;


    /**
     * @return \PayEye\Lib\PromoCode\PromoCodeResponseModel
     */
    public function removePromoCode(): PromoCodeResponseModel;
}
