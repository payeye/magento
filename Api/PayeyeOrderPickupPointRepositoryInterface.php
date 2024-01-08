<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */
namespace PayEye\PayEye\Api;

use PayEye\PayEye\Model\PayeyeOrderPickupPoint;

interface PayeyeOrderPickupPointRepositoryInterface
{
    /**
     * @param PayeyeOrderPickupPoint $entity
     * @return PayeyeOrderPickupPoint
     */
    public function save(PayeyeOrderPickupPoint $entity): PayeyeOrderPickupPoint;
}
