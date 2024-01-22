<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Healthcheck;

use PayEye\PayEye\Api\Data\HealthcheckDataInterface;

class Data implements HealthcheckDataInterface
{
    /**
     * @var string $status
     */
    private $status;

    /**
     * @inheritdoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function setStatus($value)
    {
        return $this->status = $value;
    }

}
