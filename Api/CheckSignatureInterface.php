<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

interface CheckSignatureInterface
{
    /**
     * @param array $request
     * @return bool
     */
    public function check(array $request): bool;
}
