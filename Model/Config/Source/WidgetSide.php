<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WidgetSide implements OptionSourceInterface
{
    const LEFT_SIDE_VALUE = 'left';
    const RIGHT_SIDE_VALUE = 'right';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LEFT_SIDE_VALUE, 'label' => __('Left')],
            ['value' => self::RIGHT_SIDE_VALUE, 'label' => __('Right')],
        ];
    }
}
