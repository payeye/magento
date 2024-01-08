<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use PayEye\Lib\Auth\AuthConfig;
use PayEye\Lib\Auth\HashService;
use PayEye\Lib\Model\Cart;
use PayEye\Lib\Model\Product;
use PayEye\Lib\Model\PromoCode;
use PayEye\Lib\Model\ShippingMethod;
use PayEye\PayEye\Api\GetCartHashInterface;
use PayEye\PayEye\Model\Config;

class GetCartHash implements GetCartHashInterface
{
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $request
     * @return string
     */
    public function get(array $request): string
    {
        $shopId = $this->config->getShopId();
        $publicKey = $this->config->getPublicKey();
        $privateKey = $this->config->getPrivateKey();

        $authConfig = AuthConfig::create($shopId,$publicKey,$privateKey);
        $hashService = HashService::create($authConfig);

        $products = array_map(static function (array $product) {
            return Product::createFromArray($product);
        }, $request['products']);

        $promoCodes = array_map(static function (array $promoCode) {
            return PromoCode::createFromArray($promoCode);
        }, $request['promoCodes']);

        $shippingMethods = $request['shippingMethods'] ? array_map(static function (array $shipping) {
            return ShippingMethod::createFromArray($shipping);
        }, $request['shippingMethods']) : [];

        /**
         * @param PromoCode[] $promoCodes*
         * @param ShippingMethod[] $shippingMethods*
         * @param Cart $cart
         * @param string|null $shippingId
         * @param string$currency
         * @param Product[] $products
         *
         * @return string
         */
        return $hashService->cartHash(
            $promoCodes,
            $shippingMethods,
            Cart::createFromArray($request['cart']),
            $request['shippingId'],
            $request['currency'],
            $products,
        );
    }
}
