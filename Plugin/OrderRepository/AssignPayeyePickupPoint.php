<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Plugin\OrderRepository;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PayEye\PayEye\Api\PayeyeOrderPickupPointRepositoryInterface;
use PayEye\PayEye\Model\Config;

class AssignPayeyePickupPoint
{
    private Config $config;
    private OrderExtensionFactory $orderExtensionFactory;
    private PayeyeOrderPickupPointRepositoryInterface $orderPickupPointRepository;

    /**
     * @param Config $config
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param PayeyeOrderPickupPointRepositoryInterface $orderPickupPointRepository
     */
    public function __construct(
        Config $config,
        OrderExtensionFactory $orderExtensionFactory,
        PayeyeOrderPickupPointRepositoryInterface $orderPickupPointRepository
    ) {
        $this->orderPickupPointRepository = $orderPickupPointRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->config = $config;
    }

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $orderRepository, OrderInterface $order): OrderInterface
    {
        if (!$this->config->isEnabled()) {
            return $order;
        }

        $extension = $order->getExtensionAttributes();

        if (null === $extension) {
            $extension = $this->orderExtensionFactory->create();
        }

        if ($extension->getPayeyePickupPoint()) {
            return $order;
        }

        $pickupPoint = $this->orderPickupPointRepository->get((int)$order->getEntityId());

        if ($pickupPoint) {
            $extension->setPayeyePickupPoint($pickupPoint->getPickupPoint());
            $order->setExtensionAttributes($extension);
        }

        return $order;
    }
}
