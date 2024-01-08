<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\Lib\Widget\WidgetStatusModel;

interface WidgetInterface
{
    /**
     * @param string $cartId
     * @return \PayEye\Lib\Widget\WidgetStatusModel|null
     */
    public function getStatus(string $cartId): ?WidgetStatusModel;

    /**
     * @param string $cartId
     * @return void
     */
    public function setStatus(string $cartId): void;
}
