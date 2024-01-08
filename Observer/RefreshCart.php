<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayEye\Lib\Auth\AuthConfig;
use PayEye\Lib\Auth\AuthService;
use PayEye\Lib\Auth\HashService;
use PayEye\Lib\Enum\SignatureFrom;
use PayEye\Lib\Env\Config as PayEyeLibConfig;
use PayEye\Lib\HttpClient\Model\RefreshCartRequest;
use PayEye\Lib\HttpClient\PayEyeHttpClient;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Model\Config;
use Psr\Log\LoggerInterface;

class RefreshCart implements ObserverInterface
{
    /**
     * @var PayEyeHttpClient
     */
    private $httpClient;
    private $config;
    private $logger;
    private $payeyeQuoteRepository;

    public function __construct(
        Config $config,
        LoggerInterface $logger,
        PayeyeQuoteRepositoryInterface $payeyeQuoteRepository
    ) {
        $this->payeyeQuoteRepository = $payeyeQuoteRepository;
        $this->logger = $logger;
        $this->config = $config;
        $payeyeLibConfig = PayEyeLibConfig::create($this->config->getApiUrl(), $this->config->getApiDeepLinkUrl());
        $this->httpClient = PayEyeHttpClient::create($payeyeLibConfig);
    }

    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        $cartId = null;
        switch ($event->getName()) {
            case 'checkout_cart_update_items_after':
                $quote = $observer->getEvent()->getCart()->getQuote();
                $cartId = $quote->getId();
                break;
            case 'sales_quote_remove_item':
                $quoteItem = $observer->getEvent()->getQuoteItem();
                $cartId = $quoteItem->getQuoteId();
                break;
            case 'sales_quote_product_add_after':
                foreach ($observer->getEvent()->getItems() as $item) {
                    $cartId = $item->getQuoteId();
                    break;
                }
                break;
        }

        if (!$cartId) {
            return;
        }

        $payeyeQuote = $this->payeyeQuoteRepository->getByCartId($cartId);
        if (!$payeyeQuote->getOpen()) {
            return;
        }

        try{
            $shopId = $this->config->getShopId();
            $publicKey = $this->config->getPublicKey();
            $privateKey = $this->config->getPrivateKey();

            $request = RefreshCartRequest::create(
                $payeyeQuote->getUuid(),
                $shopId,
                'CART_CHANGED');

            $authConfig = AuthConfig::create($shopId,$publicKey,$privateKey);
            $hashService = HashService::create($authConfig);

            $authService = AuthService::create(
                $hashService,
                SignatureFrom::REFRESH_CART_REQUEST,
                $request->toArray()
            );

            $this->httpClient->refreshCart($request,$authService);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
