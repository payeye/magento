<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Api;

use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Invoice;
use PayEye\Lib\Model\Shipping;
use PayEye\Lib\Order\OrderCreateResponseModel;
use PayEye\Lib\Order\OrderUpdateStatusResponseModel;

interface OrderInterface
{

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
     * @param Invoice $invoice
     * @return \PayEye\Lib\Order\OrderCreateResponseModel
     */
    public function place(string $cartId,
        array $signatureFrom,
        string $signature,
        string $cartHash,
        string $shippingId,
        string $shippingProvider,
        Billing $billing,
        Shipping $shipping,
        bool $hasInvoice = false,
        Invoice $invoice = null,
    ): OrderCreateResponseModel;

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
    ): OrderUpdateStatusResponseModel;
}
