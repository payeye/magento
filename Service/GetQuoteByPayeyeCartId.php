<?php
/**
* Copyright © PayEye sp. z o.o. All rights reserved.
*/

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Exception\CartEmptyException;
use PayEye\Lib\Exception\CartNotFoundException;
use PayEye\PayEye\Api\ErrorResponseInterface;
use PayEye\PayEye\Api\PayeyeQuoteRepositoryInterface;
use PayEye\PayEye\Api\GetQuoteByPayeyeCartIdInterface;

class GetQuoteByPayeyeCartId implements GetQuoteByPayeyeCartIdInterface
{
    private PayeyeQuoteRepositoryInterface $payeyeQuoteRepository;
    private CartRepositoryInterface $cartRepository;
    private ErrorResponseInterface $errorResponse;

    /**
     * @param PayeyeQuoteRepositoryInterface $payeyeQuoteRepository
     * @param CartRepositoryInterface $cartRepository
     * @param ErrorResponseInterface $errorResponse
     */
    public function __construct(
        PayeyeQuoteRepositoryInterface $payeyeQuoteRepository,
        CartRepositoryInterface $cartRepository,
        ErrorResponseInterface $errorResponse
    ) {
        $this->errorResponse = $errorResponse;
        $this->cartRepository = $cartRepository;
        $this->payeyeQuoteRepository = $payeyeQuoteRepository;
    }

    /**
     * @param string $cartId
     * @param bool $setOpen
     * @return CartInterface
     */
    public function getQuote(string $cartId, bool $setOpen = false): CartInterface
    {
        $quote = null;
        try {
            $payeyeQuote = $this->payeyeQuoteRepository->getByUuid($cartId);
            $quote = $this->cartRepository->getActive($payeyeQuote->getCartId());

            if (!$payeyeQuote->getOpen()) {
                $payeyeQuote->setOpen(true);
                $this->payeyeQuoteRepository->save($payeyeQuote);
            }
        } catch (NoSuchEntityException $exception) {
            $this->errorResponse->throw(new CartNotFoundException());
        }

        if ((int)$quote->getItemsCount() === 0) {
            $this->errorResponse->throw(new CartEmptyException());
        }

        return $quote;
    }
}
