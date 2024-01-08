<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use PayEye\Lib\Exception\PayEyePaymentException;
use PayEye\PayEye\Api\ErrorResponseInterface;

class ErrorResponse implements ErrorResponseInterface
{
    private GetSignature $getSignature;

    /**
     * @param GetSignature $getSignature
     */
    public function __construct(GetSignature $getSignature) {
        $this->getSignature = $getSignature;
    }

    /**
     * @param PayEyePaymentException $exception
     * @return void
     */
    public function throw(PayEyePaymentException $exception): void
    {
        $response['errorMessage'] = $exception->getMessage();
        $response['errorCode'] = $exception->getErrorCode();
        $response['signatureFrom'] = ['errorMessage', 'errorCode'];
        $response['signature'] = $this->getSignature->get($response);

        http_response_code($exception->getStatusCode());
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
