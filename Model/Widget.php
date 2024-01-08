<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrerRepositoryInterface;
use PayEye\Lib\Enum\OrderStatus;
use PayEye\Lib\Widget\WidgetStatusModel;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Api\WidgetInterface;

class Widget implements WidgetInterface
{
    private const ORDER_STATUSES = [
        MagentoOrder::STATE_PENDING_PAYMENT => OrderStatus::ORDER_CREATED,
        MagentoOrder::STATE_PROCESSING => OrderStatus::SUCCESS,
        MagentoOrder::STATE_CANCELED => OrderStatus::REJECTED
    ];
    private Config $config;
    private CartRepositoryInterface $cartRepository;
    private OrderInterfaceFactory $orderFactory;
    private PayeyeQuoteRepositoryInterface $payeyeQuoteRepository;
    private UrlInterface $urlBuilder;
    private Session $checkoutSession;

    /**
     * @param Config $config
     * @param CartRepositoryInterface $cartRepository
     * @param OrderInterfaceFactory $orderFactory
     * @param PayeyeQuoteRepositoryInterface $payeyeQuoteRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $cartRepository,
        OrderInterfaceFactory $orderFactory,
        PayeyeQuoteRepositoryInterface $payeyeQuoteRepository,
        UrlInterface $urlBuilder,
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->payeyeQuoteRepository = $payeyeQuoteRepository;
        $this->orderFactory = $orderFactory;
        $this->cartRepository = $cartRepository;
        $this->config = $config;
    }

    /**
     * @param string $cartId
     * @return WidgetStatusModel|null
     * @throws NoSuchEntityException
     */
    public function getStatus(string $cartId): ?WidgetStatusModel
    {
        if (!$this->config->isEnabled()) {
            return null;
        }

        $widgetStatus = new WidgetStatusModel();
        $payeyeQuote = $this->payeyeQuoteRepository->getByUuid($cartId);
        $quote = $this->cartRepository->get($payeyeQuote->getCartId());
        $widgetStatus->setOpen((bool)$payeyeQuote->getOpen());

        if ($quote->getReservedOrderId()) {
            try {
                $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
                if ($order->getId()) {
                    $widgetStatus->setStatus(self::ORDER_STATUSES[$order->getStatus()]);
                    $widgetStatus->setCheckoutUrl($this->urlBuilder->getUrl('checkout/onepage/success'));
                    $this->checkoutSession->setLastQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastOrderId($order->getId());
                    $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                    $this->checkoutSession->setLastOrderStatus($order->getStatus());
                }
            }catch (\Exception $e) {
            }
        }

        return $widgetStatus;
    }

    /**
     * @param string $cartId
     */
    public function setStatus(string $cartId): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $payeyeQuote = $this->payeyeQuoteRepository->getByUuid($cartId);
        $payeyeQuote->setOpen(false);
        $this->payeyeQuoteRepository->save($payeyeQuote);
    }
}
