<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PayEye\PayEye\Model\ResourceModel\PayeyeOrderPickupPoint as PayeyeOrderPickupPointResourceModel;

/**
 * PayeyeOrderPickupPoint model
 *
 * @method string getPickupPoint()
 * @method PayeyeOrderPickupPoint setPickupPoint(string $pickupPoint)
 * @method int getOrderId()
 * @method PayeyeOrderPickupPoint setOrderId(int $orderId)
 */
class PayeyeOrderPickupPoint extends AbstractModel
{
    private Random $randomDataGenerator;

    public function __construct(
        Context $context,
        Registry $registry,
        Random $randomDataGenerator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->randomDataGenerator = $randomDataGenerator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(PayeyeOrderPickupPointResourceModel::class);
    }
}
