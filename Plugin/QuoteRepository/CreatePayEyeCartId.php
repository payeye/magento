<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Plugin\QuoteRepository;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PayEye\PayEye\Model\Config;
use PayEye\PayEye\Model\ResourceModel\PayeyeQuote;
use PayEye\PayEye\Model\PayeyeQuoteFactory;
use PayEye\PayEye\Model\ResourceModel\PayeyeQuote as PayeyeQuoteResourceModel;

class CreatePayEyeCartId
{
    private Config $config;
    private PayeyeQuoteResourceModel $payeyeQuote;
    private PayeyeQuoteFactory $payeyeQuoteFactory;
    private PayeyeQuoteResourceModel $payeyeQuoteResourceModel;

    /**
     * @param Config $config
     * @param PayeyeQuote $payeyeQuote
     * @param PayeyeQuoteFactory $payeyeQuoteFactory
     * @param PayeyeQuoteResourceModel $payeyeQuoteResourceModel
     */
    public function __construct(
        Config $config,
        PayeyeQuote $payeyeQuote,
        PayeyeQuoteFactory $payeyeQuoteFactory,
        PayeyeQuoteResourceModel $payeyeQuoteResourceModel
    ) {
        $this->payeyeQuoteResourceModel = $payeyeQuoteResourceModel;
        $this->payeyeQuoteFactory = $payeyeQuoteFactory;
        $this->payeyeQuote = $payeyeQuote;
        $this->config = $config;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param $result
     * @param CartInterface $cart
     * @return void
     */
    public function afterSave(CartRepositoryInterface $subject, $result, CartInterface $cart): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->payeyeQuote->getUuid(((int)$cart->getId()))) {
            $payeyeQuote = $this->payeyeQuoteFactory->create();
            $payeyeQuote->setCartId((int)$cart->getId());
            $payeyeQuote->setOpen(false);
            $this->payeyeQuoteResourceModel->save($payeyeQuote);
        }
    }
}
