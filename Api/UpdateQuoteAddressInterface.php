<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use Magento\Quote\Api\Data\CartInterface as MagentoCartInterface;
use PayEye\Lib\Cart\CartRequestModel;

interface UpdateQuoteAddressInterface
{
    /**
     * @param MagentoCartInterface $quote
     * @param CartRequestModel $request
     * @return void
     */
    public function update(MagentoCartInterface $quote, CartRequestModel $request): void;
}
