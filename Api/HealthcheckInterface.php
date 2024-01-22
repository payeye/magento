<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\PayEye\Api\Data\HealthcheckDataInterface;

interface HealthcheckInterface
{
    /**
     * @return HealthcheckDataInterface
     */
    public function get(): HealthcheckDataInterface;
}
