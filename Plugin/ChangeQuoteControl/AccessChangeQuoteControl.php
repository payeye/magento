<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Plugin\ChangeQuoteControl;

use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Quote\Api\Data\CartInterface;

class AccessChangeQuoteControl
{
    public function afterIsAllowed(ChangeQuoteControlInterface $subject, bool $result, CartInterface $quote): bool
    {
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getIsPayeye()) {
            return true;
        }

        return $result;
    }
}
