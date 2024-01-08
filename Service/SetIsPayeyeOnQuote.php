<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use PayEye\PayEye\Api\SetIsPayeyeOnQuoteInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

class SetIsPayeyeOnQuote implements SetIsPayeyeOnQuoteInterface
{
    private CartExtensionFactory $cartExtensionFactory;

    /**
     * @param CartExtensionFactory $cartExtensionFactory
     */
    public function __construct(CartExtensionFactory $cartExtensionFactory) {
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * @param CartInterface $quote
     */
    public function set(CartInterface $quote): void
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->cartExtensionFactory->create();
        }
        $extensionAttributes->setIsPayeye(true);
        $quote->setExtensionAttributes($extensionAttributes);
    }
}
