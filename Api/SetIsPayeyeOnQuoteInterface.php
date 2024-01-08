<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */
namespace PayEye\PayEye\Api;

use Magento\Quote\Api\Data\CartInterface;

interface SetIsPayeyeOnQuoteInterface
{
    /**
     * @param CartInterface $quote
     */
    public function set(CartInterface $quote): void;
}
