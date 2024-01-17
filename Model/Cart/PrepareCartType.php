<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Enum\CartType;

class PrepareCartType
{
    /**
     * @param CartInterface $quote
     * @return string|null
     */
    public function get(CartInterface $quote): ?string
    {
        $hasPhysical = false;
        $hasVirtual = false;

        foreach ($quote->getAllVisibleItems() as $item) {
            switch ($item->getProduct()->getTypeId()) {
                case 'simple':
                case 'grouped':
                case 'configurable':
                case 'bundle':
                    $hasPhysical = true;
                    break;
                case 'virtual':
                case 'downloadable':
                    $hasVirtual = true;
                    break;
            }

            if ($hasPhysical && $hasVirtual) {
                return CartType::MIXED;
            }
        }

        if ($hasPhysical) {
            return CartType::STANDARD;
        }

        if ($hasVirtual) {
            return CartType::VIRTUAL;
        }

        return null;
    }
}
