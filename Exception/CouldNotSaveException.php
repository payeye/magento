<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Exception;

use PayEye\Lib\Enum\ErrorCode;
use PayEye\Lib\Enum\HttpStatus;
use PayEye\Lib\Exception\PayEyePaymentException;

class CouldNotSaveException extends PayEyePaymentException
{
    protected $statusCode = HttpStatus::BAD_REQUEST;
    protected $errorCode = ErrorCode::Magento2;
}
