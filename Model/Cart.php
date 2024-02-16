<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use PayEye\Lib\Enum\CartType;
use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Shipping;
use PayEye\Lib\Cart\CartResponseModel;
use PayEye\Lib\PromoCode\PromoCodeRequestModel;
use PayEye\Lib\PromoCode\PromoCodeResponseModel;
use PayEye\Lib\Exception\InvalidCouponException;
use PayEye\Lib\Exception\SignatureNotMatchedException;
use PayEye\PayEye\Api\CartInterface;
use PayEye\PayEye\Api\GetCartHashInterface;
use PayEye\PayEye\Api\GetSignatureInterface;
use PayEye\PayEye\Api\GetQuoteByPayeyeCartIdInterface;
use PayEye\PayEye\Api\CheckSignatureInterface;
use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\PayEye\Api\SetIsPayeyeOnQuoteInterface;
use PayEye\PayEye\Api\UpdateQuoteAddressInterface;
use PayEye\PayEye\Exception\ModuleDisabledException;
use PayEye\PayEye\Model\Cart\PrepareQuoteData;
use PayEye\PayEye\Model\Cart\PrepareCartRequestModel;
use Psr\Log\LoggerInterface;
class Cart implements CartInterface
{
    private Config $config;
    private CartRepositoryInterface $cartRepository;
    private GetCartHashInterface $getCartHash;
    private GetSignatureInterface $getSignature;
    private GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId;
    private CheckSignatureInterface $checkSignature;
    private PrepareQuoteData $prepareQuoteData;
    private UpdateQuoteAddressInterface $updateQuoteAddress;
    private ErrorResponseInterface $errorResponse;
    private PrepareCartRequestModel $prepareCartRequestModel;
    private SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote;
    private RestRequest $request;
    private LoggerInterface $logger;

    /**
     * @param Config $config
     * @param CartRepositoryInterface $cartRepository
     * @param GetCartHashInterface $getCartHash
     * @param GetSignatureInterface $getSignature
     * @param GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId
     * @param CheckSignatureInterface $checkSignature
     * @param PrepareQuoteData $prepareQuoteData
     * @param UpdateQuoteAddressInterface $updateQuoteAddress
     * @param ErrorResponseInterface $errorResponse
     * @param PrepareCartRequestModel $prepareCartRequestModel
     * @param SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote
     * @param RestRequest $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $cartRepository,
        GetCartHashInterface $getCartHash,
        GetSignatureInterface $getSignature,
        GetQuoteByPayeyeCartIdInterface $getQuoteByPayeyeCartId,
        CheckSignatureInterface $checkSignature,
        PrepareQuoteData $prepareQuoteData,
        UpdateQuoteAddressInterface $updateQuoteAddress,
        ErrorResponseInterface $errorResponse,
        PrepareCartRequestModel $prepareCartRequestModel,
        SetIsPayeyeOnQuoteInterface $setIsPayeyeOnQuote,
        RestRequest $request,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->setIsPayeyeOnQuote = $setIsPayeyeOnQuote;
        $this->prepareCartRequestModel = $prepareCartRequestModel;
        $this->errorResponse = $errorResponse;
        $this->updateQuoteAddress = $updateQuoteAddress;
        $this->prepareQuoteData = $prepareQuoteData;
        $this->checkSignature = $checkSignature;
        $this->getQuoteByPayeyeCartId = $getQuoteByPayeyeCartId;
        $this->getSignature = $getSignature;
        $this->getCartHash = $getCartHash;
        $this->cartRepository = $cartRepository;
        $this->config = $config;
    }

    /**
     * @param string $cartId
     * @param string[] $signatureFrom
     * @param string $signature
     * @param string|null $shippingId
     * @param string|null $shippingProvider
     * @param Billing|null $billing
     * @param Shipping|null $shipping
     * @return \PayEye\Lib\Cart\CartResponseModel
     */
    public function get(
        string $cartId,
        array $signatureFrom,
        string $signature,
        string $shippingId = null,
        string $shippingProvider = null,
        Billing $billing = null,
        Shipping $shipping = null
    ): CartResponseModel {
        $request = $this->prepareCartRequestModel->execute(
            $cartId,
            $signatureFrom,
            $signature,
            $shippingId,
            $shippingProvider,
            $billing,
            $shipping
        );
        $this->checkIfCanProcess($request->toArray());
        $quote = $this->getQuoteByPayeyeCartId->getQuote($request->getCartId(), true);
        $this->setIsPayeyeOnQuote->set($quote);

        $this->updateQuoteAddress->update($quote, $request);

        $response = $this->prepareQuoteData->get($quote, (bool)$request->getShipping());

        $response['supportedFeatures'] = ['INVOICE'];
        $response['cartHash'] = $this->getCartHash->get($response);
        $response['signatureFrom'] = [
            "cart",
            "products",
            "currency",
            "promoCodes",
            "shippingMethods",
            "shop",
            "shippingId",
            "cartHash"
        ];
        $response['signature'] = $this->getSignature->get($response);

        return CartResponseModel::createFromArray($response);
    }

    /**
     * @param string $cartId
     * @param string $promoCode
     * @param string[] $signatureFrom
     * @param string $signature
     * @return \PayEye\Lib\PromoCode\PromoCodeResponseModel
     */
    public function setPromoCode(
        string $cartId,
        string $promoCode,
        array $signatureFrom,
        string $signature
    ): PromoCodeResponseModel {
        $request = PromoCodeRequestModel::createFromArray([
            'cartId' => $cartId,
            'promoCode' => $promoCode,
            'signatureFrom' => $signatureFrom,
            'signature' => $signature
        ]);

        $this->checkIfCanProcess($request->toArray());
        $quote = $this->getQuoteByPayeyeCartId->getQuote($request->getCartId());
        $this->setIsPayeyeOnQuote->set($quote);
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($request->getPromoCode());
            $this->cartRepository->save($quote->collectTotals());
        }  catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->errorResponse->throw(new InvalidCouponException());
        }

        if ($quote->getCouponCode() !== $request->getPromoCode()) {
            $this->errorResponse->throw(new InvalidCouponException());
        }

        $response['status'] = 'OK';
        $response['signatureFrom'] = ['status'];
        $response['signature'] = $this->getSignature->get($response);

        return PromoCodeResponseModel::createFromArray($response);
    }

    /**
     * @return \PayEye\Lib\PromoCode\PromoCodeResponseModel
     */
    public function removePromoCode(): PromoCodeResponseModel
    {
        $request = PromoCodeRequestModel::createFromArray($this->request->getBodyParams());

        $this->checkIfCanProcess($request->toArray());
        $quote = $this->getQuoteByPayeyeCartId->getQuote($request->getCartId());
        $this->setIsPayeyeOnQuote->set($quote);

        try {
            $quote->setCouponCode('');
            $this->cartRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->errorResponse->throw(new InvalidCouponException());
        }

        $response['status'] = 'OK';
        $response['signatureFrom'] = ['status'];
        $response['signature'] = $this->getSignature->get($response);

        return PromoCodeResponseModel::createFromArray($response);
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
