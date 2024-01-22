<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api\Data;

interface HealthcheckDataInterface
{
    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $value
     * @return string|null
     */
    public function setStatus($value);
}
