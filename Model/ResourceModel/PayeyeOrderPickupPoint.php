<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PayeyeOrderPickupPoint extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('payeye_order_pickup_point', 'entity_id');
    }
}
