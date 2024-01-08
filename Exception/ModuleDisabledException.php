<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Exception;

use PayEye\Lib\Enum\ErrorCode;
use PayEye\Lib\Enum\HttpStatus;
use PayEye\Lib\Exception\PayEyePaymentException;

class ModuleDisabledException extends PayEyePaymentException
{
    protected $message = 'Module PayEye is disabled';
    protected $statusCode = HttpStatus::BAD_REQUEST;
    protected $errorCode = ErrorCode::Magento2;
}
