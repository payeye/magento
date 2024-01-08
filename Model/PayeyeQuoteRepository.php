<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Exception\CouldNotSaveException;
use PayEye\PayEye\Model\ResourceModel\PayeyeQuote as PayeyeQuoteResourceModel;

class PayeyeQuoteRepository implements PayeyeQuoteRepositoryInterface
{
    private PayeyeQuoteFactory $payeyeQuoteFactory;
    private PayeyeQuoteResourceModel $payeyeQuoteResourceModel;
    private ErrorResponseInterface $errorResponse;

    /**
     * @param PayeyeQuoteFactory $payeyeQuoteFactory
     * @param PayeyeQuote $payeyeQuoteResourceModel
     * @param ErrorResponseInterface $errorResponse
     */
    public function __construct(
        PayeyeQuoteFactory $payeyeQuoteFactory,
        PayeyeQuoteResourceModel $payeyeQuoteResourceModel,
        ErrorResponseInterface $errorResponse
    ) {
        $this->errorResponse = $errorResponse;
        $this->payeyeQuoteResourceModel = $payeyeQuoteResourceModel;
        $this->payeyeQuoteFactory = $payeyeQuoteFactory;
    }

    public function getByUuid(string $uuid): PayeyeQuote
    {
        $entity = $this->payeyeQuoteFactory->create();
        $this->payeyeQuoteResourceModel->load($entity, $uuid, 'uuid');
        return $entity;
    }

    public function getByCartId(string $cartId): PayeyeQuote
    {
        $entity = $this->payeyeQuoteFactory->create();
        $this->payeyeQuoteResourceModel->load($entity, $cartId, 'cart_id');
        return $entity;
    }

    public function save(PayeyeQuote $entity): PayeyeQuote
    {
        try {
            $this->payeyeQuoteResourceModel->save($entity);
        } catch (\Exception $e) {
            $this->errorResponse->throw(new CouldNotSaveException(__('Could not save the payeye quote.'), $e));
        }

        return $entity;
    }
}
