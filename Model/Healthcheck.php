<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use PayEye\PayEye\Api\Data\HealthcheckDataInterface;
use PayEye\PayEye\Api\Data\HealthcheckDataInterfaceFactory;

class Healthcheck
{
    /**
     * @var HealthcheckDataInterfaceFactory $healthcheckData
     */
    private $healthcheckData;

    /**
     * @param HealthcheckDataInterfaceFactory $healthcheckData
     */
    public function __construct(
        HealthcheckDataInterfaceFactory $healthcheckData,
    ) {
        $this->healthcheckData = $healthcheckData;
    }

    /**
     * @return string
     */
    public function get(): HealthcheckDataInterface
    {
        /** @var HealthcheckDataInterface $data */
        $data = $this->healthcheckData->create();
        $data->setStatus('Up');

        return $data;
    }
}
