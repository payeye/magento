<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\PayEye\Api\PayeyeOrderPickupPointRepositoryInterface;
use PayEye\PayEye\Exception\CouldNotSaveException;
use PayEye\PayEye\Model\ResourceModel\PayeyeOrderPickupPoint as PayeyeOrderPickupPointResourceModel;

class PayeyeOrderPickupPointRepository implements PayeyeOrderPickupPointRepositoryInterface
{
    private PayeyeOrderPickupPointResourceModel $payeyeOrderPickupPointResource;
    private PayeyeOrderPickupPointFactory $payeyeOrderPickupPointFactory;
    private ErrorResponseInterface $errorResponse;

    /**
     * @param PayeyeOrderPickupPointResourceModel $payeyeOrderPickupPointResource
     * @param PayeyeOrderPickupPointFactory $payeyeOrderPickupPointFactory
     * @param ErrorResponseInterface $errorResponse
     */
    public function __construct(
        PayeyeOrderPickupPointResourceModel $payeyeOrderPickupPointResource,
        PayeyeOrderPickupPointFactory $payeyeOrderPickupPointFactory,
        ErrorResponseInterface $errorResponse
    ) {
        $this->errorResponse = $errorResponse;
        $this->payeyeOrderPickupPointFactory = $payeyeOrderPickupPointFactory;
        $this->payeyeOrderPickupPointResource = $payeyeOrderPickupPointResource;
    }

    /**
     * @param PayeyeOrderPickupPoint $entity
     * @return PayeyeOrderPickupPoint
     */
    public function save(PayeyeOrderPickupPoint $entity): PayeyeOrderPickupPoint
    {
        try {
            $this->payeyeOrderPickupPointResource->save($entity);
        } catch (\Exception $e) {
            $this->errorResponse->throw(new CouldNotSaveException(__('Could not save the payeye pickup point.'), $e));
        }

        return $entity;
    }

    /**
     * @param int $orderId
     * @return PayeyeOrderPickupPoint
     */
    public function get(int $orderId): PayeyeOrderPickupPoint
    {
        $entity = $this->payeyeOrderPickupPointFactory->create();
        $this->payeyeOrderPickupPointResource->load($entity, $orderId, 'order_id');
        return $entity;
    }
}
