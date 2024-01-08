<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\Lib\Exception\PayEyePaymentException;

interface ErrorResponseInterface
{

    /**
     * @param PayEyePaymentException $exception
     * @return void
     */
    public function throw(PayEyePaymentException $exception): void;
}
