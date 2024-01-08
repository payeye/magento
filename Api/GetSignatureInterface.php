<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

interface GetSignatureInterface
{
    /**
     * @param array $request
     * @return string
     */
    public function get(array $request): string;
}
