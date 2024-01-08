<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Service\AmountService;

class PrepareTotalsData
{
    private AmountService $amountService;

    /**
     * @param AmountService $amountService
     */
    public function __construct(AmountService $amountService) {
        $this->amountService = $amountService;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function get(CartInterface $quote): array
    {
        $discount = 0.0;
        foreach ($quote->getItems() as $item) {
            $discount += $item->getDiscountAmount();
        }

        $cart['total'] = $this->amountService->convertFloatToInteger($quote->getBaseGrandTotal());
        $cart['regularTotal'] = $this->amountService->convertFloatToInteger($quote->getBaseGrandTotal() + $discount);
        $cart['products'] = $this->amountService->convertFloatToInteger($quote->getBaseSubtotal() - $discount);
        $cart['regularProducts'] = $this->amountService->convertFloatToInteger($quote->getBaseSubtotal());
        $cart['discount'] = $this->amountService->convertFloatToInteger($discount);

        return $cart;
    }
}
