<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\CustomerData;

use chillerlan\QRCode\QRCode;
use Magento\Checkout\Model\Session;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Model\Config;
use PayEye\PayEye\Model\PayeyeQuote;
use Zend_Currency;
use Magento\Framework\Currency\Data\Currency as CurrencyData;

class PayEyeSection extends DataObject implements SectionSourceInterface
{
    private $cart;
    private $payeyeQuote;
    private Session $session;
    private Config $config;
    private UrlInterface $urlBuilder;
    private PayeyeQuoteRepositoryInterface $payeyeQuoteRepository;
    private Currency $currency;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param Session $session
     * @param Config $config
     * @param UrlInterface $urlBuilder
     * @param PayeyeQuoteRepositoryInterface $payeyeQuoteRepository
     * @param Currency $currency
     * @param array $data
     */
    public function __construct(
        Session $session,
        Config $config,
        UrlInterface $urlBuilder,
        PayeyeQuoteRepositoryInterface $payeyeQuoteRepository,
        Currency $currency,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->currency = $currency;
        $this->payeyeQuoteRepository = $payeyeQuoteRepository;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getSectionData(): array
    {
        if (!$this->config->isEnabled() ||
            !$this->getPayeyeQuote($this->getCartId())->getUuid() ||
            !$this->getCount() || $this->getCount() === 0
        ) {
            return [];
        }

        return [
            'apiVersion' => $this->config->getApiVersion(),
            'deepLink' => $this->getDeepLink(),
            'cart' => [
                'id' => $this->getCartUUid(),
                'open' => $this->getOpenStatus(),
                'price' => $this->getPrice(),
                'regularPrice' => $this->getRegularPrice(),
                'qr' => $this->getQr(),
                'count' => $this->getCount(),
                'url' => $this->getCartUrl()
            ]
        ];
    }

    /**
     * @return string|null
     */
    private function getDeepLink(): ?string
    {
        return sprintf('%s?cartId=%s&shopId=%s',
            $this->config->getApiDeepLinkUrl(),
            $this->getCartUuid(),
            $this->config->getShopId());
    }

    /**
     * @return string
     */
    private function getCartUuid(): string
    {
        return $this->getPayeyeQuote($this->getCartId())->getUuid() ?? '';
    }

    /**
     * @param int $cartId
     * @return PayeyeQuote
     */
    private function getPayeyeQuote(int $cartId): PayeyeQuote
    {
        if (!$this->payeyeQuote) {
            $this->payeyeQuote = $this->payeyeQuoteRepository->getByCartId($cartId);
        }

        return $this->payeyeQuote;
    }

    /**
     * @return int
     */
    private function getCartId(): int
    {
        return (int)$this->getCart()->getId();
    }

    /**
     * @return bool
     */
    private function getOpenStatus(): bool
    {
        return $this->getPayeyeQuote($this->getCartId())->getOpen() ?? '';
    }

    /**
     * @return string|null
     */
    private function getPrice(): ?string
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.6', '<')) {
            return $this->currency->format(
                $this->getCart()->getSubtotalWithDiscount(),
                ['display'=>Zend_Currency::NO_SYMBOL],
                false
            );
        }

        return $this->currency->format(
            $this->getCart()->getSubtotalWithDiscount(),
            ['display'=>CurrencyData::NO_SYMBOL],
            false
        );
    }

    /**
     * @return string|null
     */
    private function getRegularPrice(): ?string
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.6', '<')) {
            return $this->currency->format(
                $this->getCart()->getSubtotal(),
                ['display' => Zend_Currency::NO_SYMBOL],
                false
            );
        }

        return $this->currency->format(
            $this->getCart()->getSubtotal(),
            ['display'=>CurrencyData::NO_SYMBOL],
            false
        );
    }

    /**
     * @return string|null
     */
    private function getQr(): ?string
    {
        return (new QRCode())->render($this->getDeepLink());
    }

    /**
     * @return int|null
     */
    private function getCount(): ?int
    {
        return $this->getCart()->getItemsCount();
    }

    /**
     * @return string
     */
    private function getCartUrl(): string
    {
        return $this->urlBuilder->getUrl('checkout/cart');
    }

    /**
     * @return CartInterface|Quote
     */
    private function getCart()
    {
        if (!$this->cart) {
            $this->cart = $this->session->getQuote();
        }

        return $this->cart;
    }
}
