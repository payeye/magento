<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Enum\PromoCodeType;

class PreparePromoCodes
{
    private const MAPPED_PROMO_CODE_TYPES = [
        RuleInterface::DISCOUNT_ACTION_BY_PERCENT            => PromoCodeType::PERCENTAGE_DISCOUNT_VALUE,
        RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT          => PromoCodeType::CONSTANT_DISCOUNT_VALUE,
        RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART => PromoCodeType::CONSTANT_DISCOUNT_VALUE,
        RuleInterface::DISCOUNT_ACTION_BUY_X_GET_Y           => PromoCodeType::CONSTANT_DISCOUNT_VALUE,
    ];
    private RuleRepositoryInterface $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository) {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function get(CartInterface $quote): array
    {
        $promoCodes = [];
        if ($quote->getAppliedRuleIds()) {
            foreach (explode(',',$quote->getAppliedRuleIds()) as $ruleId) {
                $rule = $this->ruleRepository->getById($ruleId);

                if ($rule->getCouponType() !== RuleInterface::COUPON_TYPE_SPECIFIC_COUPON) {
                    continue;
                }

                $promoCode = [];
                $promoCode['code'] = $quote->getCouponCode();
                $promoCode['type'] = self::MAPPED_PROMO_CODE_TYPES[$rule->getSimpleAction()];
                $promoCode['value'] = (int)$rule->getDiscountAmount();
                $promoCode['freeDelivery'] = (bool)$rule->getSimpleFreeShipping();
                $promoCode['payeyeCode'] = false;

                $promoCodes[] = $promoCode;
            }
        }

        return $promoCodes;
    }
}
