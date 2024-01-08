<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayEye\PayEye\Model\Config;

class AfterPlaceOrderObserver implements ObserverInterface
{
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        /** @var Payment $payment */
        $payment = $observer->getData('payment');
        $method = $payment->getMethod();
        if ($method !== 'payeye') {
            return;
        }

        $this->assignStatus($payment);
    }

    /**
     * @param Payment $payment
     *
     * @return void
     */
    private function assignStatus(Payment $payment)
    {
        $order = $payment->getOrder();
        $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
    }
}
