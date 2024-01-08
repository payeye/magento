<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */
namespace PayEye\PayEye\Api;

use PayEye\PayEye\Model\PayeyeQuote;

interface PayeyeQuoteRepositoryInterface
{
    /**
     * @param string $uuid
     * @return PayeyeQuote
     */
    public function getByUuid(string $uuid): PayeyeQuote;

    /**
     * @param string $cartId
     * @return PayeyeQuote
     */
    public function getByCartId(string $cartId): PayeyeQuote;

    /**
     * @param PayeyeQuote $entity
     * @return PayeyeQuote
     */
    public function save(PayeyeQuote $entity): PayeyeQuote;
}
