<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface as MagentoCartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Store\Model\StoreManagerInterface;
use PayEye\Lib\Exception\OrderFailedException;
use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\InvoiceDetails;
use PayEye\Lib\Model\Shipping;
use PayEye\Lib\Service\AmountService;
use PayEye\Lib\Order\OrderCreateResponseModel;
use PayEye\Lib\Order\OrderUpdateStatusRequestModel;
use PayEye\Lib\Order\OrderUpdateStatusResponseModel;
use PayEye\Lib\Enum\OrderStatus;
use PayEye\Lib\Exception\CartContentNotMatchedException;
use PayEye\Lib\Exception\OrderNotFoundException;
use PayEye\Lib\Exception\OrderPaidException;
use PayEye\Lib\Exception\ShippingProviderNotMatchedException;
use PayEye\Lib\Exception\SignatureNotMatchedException;
use PayEye\PayEye\Api\CheckSignatureInterface;
use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\PayEye\Api\GetQuoteByPayeyeCartIdInterface;
use PayEye\PayEye\Api\GetSignatureInterface;
use PayEye\PayEye\Api\OrderInterface;
use PayEye\PayEye\Api\SetIsPayeyeOnQuoteInterface;
use PayEye\PayEye\Api\GetCartHashInterface;
use PayEye\PayEye\Exception\CouldNotSaveException;
use PayEye\PayEye\Exception\ModuleDisabledException;
use PayEye\PayEye\Model\Cart\PrepareQuoteData;
use PayEye\PayEye\Model\Order\PrepareOrderCreateRequestModel;
use Psr\Log\LoggerInterface;

class Order implements OrderInterface
{
    private const ORDER_STATUSES = [
        OrderStatus::SUCCESS => MagentoOrder::STATE_COMPLETE,
        OrderStatus::REJECTED => MagentoOrder::STATE_CANCELED
    ];
    private Config $config;
    private StoreManagerInterface $storeManager;
    private GetCartHashInterface $getCartHash;
    private GetSignatureInterface $getSignature;
    private GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId;
    private CheckSignatureInterface $checkSignature;
    private PrepareQuoteData $prepareQuoteData;
    private CartManagementInterface $cartManagement;
    private UrlInterface $urlBuilder;
    private OrderRepositoryInterface $orderRepository;
    private ErrorResponseInterface $errorResponse;
    private OrderUpdateStatusRequestModel $orderUpdateStatusRequestModel;
    private PrepareOrderCreateRequestModel $prepareOrderCreateRequestModel;
    private SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote;
    private AmountService $amountService;
    private OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository;
    private LoggerInterface $logger;
    private Data $checkoutHelper;
    private CartRepositoryInterface $cartRepository;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param GetCartHashInterface $getCartHash
     * @param GetSignatureInterface $getSignature
     * @param GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId
     * @param CheckSignatureInterface $checkSignature
     * @param PrepareQuoteData $prepareQuoteData
     * @param CartManagementInterface $cartManagement
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param ErrorResponseInterface $errorResponse
     * @param OrderUpdateStatusRequestModel $orderUpdateStatusRequestModel
     * @param PrepareOrderCreateRequestModel $prepareOrderCreateRequestModel
     * @param SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote
     * @param AmountService $amountService
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param LoggerInterface $logger
     * @param Data $checkoutHelper
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        GetCartHashInterface $getCartHash,
        GetSignatureInterface $getSignature,
        GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId,
        CheckSignatureInterface $checkSignature,
        PrepareQuoteData $prepareQuoteData,
        CartManagementInterface $cartManagement,
        UrlInterface $urlBuilder,
        OrderRepositoryInterface $orderRepository,
        ErrorResponseInterface $errorResponse,
        OrderUpdateStatusRequestModel $orderUpdateStatusRequestModel,
        PrepareOrderCreateRequestModel $prepareOrderCreateRequestModel,
        SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote,
        AmountService $amountService,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        LoggerInterface $logger,
        Data $checkoutHelper,
        CartRepositoryInterface $cartRepository
    ) {
        $this->logger = $logger;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->amountService = $amountService;
        $this->setIsPayeyeOnQuote = $setIsPayeyeOnQuote;
        $this->prepareOrderCreateRequestModel = $prepareOrderCreateRequestModel;
        $this->orderUpdateStatusRequestModel = $orderUpdateStatusRequestModel;
        $this->errorResponse = $errorResponse;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = $urlBuilder;
        $this->cartManagement = $cartManagement;
        $this->prepareQuoteData = $prepareQuoteData;
        $this->checkSignature = $checkSignature;
        $this->getQuoteByPayeyeCartId = $getQuoteByPayeyeCartId;
        $this->getSignature = $getSignature;
        $this->getCartHash = $getCartHash;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->checkoutHelper = $checkoutHelper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param string $cartId
     * @param string[] $signatureFrom
     * @param string $signature
     * @param string $cartHash
     * @param string $shippingId
     * @param string $shippingProvider
     * @param Billing $billing
     * @param Shipping $shipping
     * @param bool $hasInvoice
     * @param InvoiceDetails $invoiceDetails
     * @return \PayEye\Lib\Order\OrderCreateResponseModel
     */
    public function place(
        string $cartId,
        array $signatureFrom,
        string $signature,
        string $cartHash,
        string $shippingId,
        string $shippingProvider,
        Billing $billing,
        Shipping $shipping,
        bool $hasInvoice = false,
        InvoiceDetails $invoiceDetails = null,
    ): OrderCreateResponseModel {
        $request = $this->prepareOrderCreateRequestModel->execute(
            $cartId,
            $signatureFrom,
            $signature,
            $cartHash,
            $shippingId,
            $shippingProvider,
            $billing,
            $shipping,
            $hasInvoice,
            $invoiceDetails
        );

        $this->checkIfCanProcess($request->toArray());
        $quote = $this->getQuoteByPayeyeCartId->getQuote($cartId);

        if ($request->hasInvoice()) {
            /** @var \Magento\Quote\Api\Data\AddressInterface $billingAddress */
            $billingAddress = $quote->getBillingAddress();
            $billingAddress
                ->setVatId($request->getInvoiceDetails()->getTaxId())
                ->setCompany($request->getInvoiceDetails()->getCompanyName());
            $quote->setBillingAddress($billingAddress);
            $this->cartRepository->save($quote);

            $quote = $this->getQuoteByPayeyeCartId->getQuote($cartId);
        }

        $this->setIsPayeyeOnQuote->set($quote);

        $this->compareCartHash($request->getCartHash(), $quote, (bool)$request->getShipping());
        $this->validateShippingId($request->getShippingId(), $request->getShippingProvider());

        try {
            if (!$quote->getCheckoutMethod()) {
                if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                    $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
                } else {
                    $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
                }
            }

            $orderId = $this->cartManagement->placeOrder($quote->getId());
        } catch (LocalizedException $e) {
            $this->errorResponse->throw(new CouldNotSaveException($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->errorResponse->throw(new CouldNotSaveException(
                'An error occurred on the server. Please try to place the order again.'));
        }

        $order = $this->orderRepository->get($orderId);
        if ($request->getShipping()->getPickupPoint()) {
            $pickupPointJson = json_encode($request->getShipping()->getPickupPoint()->toArray());
            $comment = __('Order was placed with selected pickup point: %1',$pickupPointJson);
            $commentModel = $order->addCommentToStatusHistory($comment);
            $this->orderStatusHistoryRepository->save($commentModel);
        }

        $response['checkoutUrl'] = $this->urlBuilder->getUrl('checkout/onepage/success');
        $response['orderId'] = $order->getIncrementId();
        $response['totalAmount'] = $this->amountService->convertFloatToInteger($order->getGrandTotal());
        $response['cartAmount'] = $this->amountService->convertFloatToInteger($order->getGrandTotal() - $order->getShippingAmount());
        $response['shippingAmount'] = $this->amountService->convertFloatToInteger($order->getShippingAmount());
        $response['currency'] = $order->getBaseCurrencyCode() ?:
            $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $response['signatureFrom'] = [
            'checkoutUrl',
            'orderId',
            'totalAmount',
            'cartAmount',
            'shippingAmount',
            'currency'
        ];
        $response['signature'] = $this->getSignature->get($response);

        return OrderCreateResponseModel::createFromArray($response);
    }

    /**
     * @param string $orderId
     * @param string $status
     * @param string $signature
     * @param string[] $signatureFrom
     * @return \PayEye\Lib\Order\OrderUpdateStatusResponseModel
     */
    public function changeStatus(
        string $orderId,
        string $status,
        string $signature,
        array $signatureFrom
    ): OrderUpdateStatusResponseModel {
        $request = $this->orderUpdateStatusRequestModel;
        $request->setOrderId($orderId);
        $request->setStatus($status);
        $request->setSignatureFrom($signatureFrom);
        $request->setSignature($signature);

        $this->checkIfCanProcess($request->toArray());

        try {
            $order = $this->orderRepository->get($orderId);

            switch ($order->getStatus()){
                case MagentoOrder::STATE_COMPLETE:
                case MagentoOrder::STATE_PROCESSING:
                    $this->errorResponse->throw(new OrderPaidException());
                    break;
                case MagentoOrder::STATE_CANCELED:
                    $this->errorResponse->throw(new OrderFailedException());
                    break;
            }

            switch ($request->getStatus()) {
                case OrderStatus::SUCCESS:
                    $payment = $order->getPayment();
                    $payment->capture();
                    break;
                case OrderStatus::REJECTED:
                    $order->setState(MagentoOrder::STATE_CANCELED)->setStatus(MagentoOrder::STATE_CANCELED);
                    break;
            }

            $this->orderRepository->save($order);

        } catch (NoSuchEntityException $e) {
            $this->errorResponse->throw(new OrderNotFoundException());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->errorResponse->throw(new CouldNotSaveException(
                'An error occurred on the server. Please try update status again'));
        }

        $response['status'] = 'OK';
        $response['signatureFrom'] = ['status'];
        $response['signature'] = $this->getSignature->get($response);

        return OrderUpdateStatusResponseModel::createFromArray($response);
    }

    /**
     * @param string $requestCartHash
     * @param MagentoCartInterface $quote
     * @param bool $shippingAddressSended
     * @return void
     */
    private function compareCartHash(
        string $requestCartHash,
        MagentoCartInterface $quote,
        bool $shippingAddressSended
    ): void {
        $quoteData = $this->prepareQuoteData->get($quote, $shippingAddressSended);
        $cartHash = $this->getCartHash->get($quoteData);

        if ($requestCartHash !== $cartHash) {
            $this->errorResponse->throw(new CartContentNotMatchedException());
        }
    }

    /**
     * @param string $shippingId
     * @param string $shippingProvider
     * @return void
     */
    private function validateShippingId(string $shippingId, string $shippingProvider): void
    {
        $mappedShippingMethods = $this->config->getShippingMethods();
        foreach ($mappedShippingMethods as $magentoMethod => $payEyeMethod) {
            if ($payEyeMethod === $shippingProvider) {
                if ($magentoMethod !== $shippingId) {
                    $this->errorResponse->throw(new ShippingProviderNotMatchedException());
                }
                break;
            }
        }
    }

    /**
     * @param array $request
     * @return void
     */
    private function checkIfCanProcess(array $request): void
    {
        if (!$this->config->isEnabled()) {
            $this->errorResponse->throw(new ModuleDisabledException());
        }

        if (!$this->checkSignature->check($request)) {
            $this->errorResponse->throw(new SignatureNotMatchedException());
        }
    }
}
