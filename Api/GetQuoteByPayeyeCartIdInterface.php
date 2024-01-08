<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use Magento\Quote\Api\Data\CartInterface;

interface GetQuoteByPayeyeCartIdInterface
{
    /**
     * @param string $cartId
     * @param bool $setOpen
     * @return CartInterface
     */
    public function getQuote(string $cartId, bool $setOpen = false): CartInterface;
}
