<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PayeyeShippingMethod implements OptionSourceInterface
{
    const INPOST = 'INPOST';
    const DHL = 'DHL';
    const COURIER = 'COURIER';
    const SELF_PICKUP = 'SELF_PICKUP';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['label' => $label, 'value' => $value];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::INPOST => self::INPOST,
            self::DHL => self::DHL,
            self::COURIER => self::COURIER,
            self::SELF_PICKUP => self::SELF_PICKUP
        ];
    }
}
