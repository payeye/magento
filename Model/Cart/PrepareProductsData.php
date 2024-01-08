<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Quote\Api\Data\CartInterface;
use PayEye\Lib\Service\AmountService;

class PrepareProductsData
{
    private AmountService $amountService;
    private ProductRepositoryInterface $productRepository;
    private ProductHelper $productHelper;

    /**
     * @param AmountService $amountService
     * @param ProductRepositoryInterface $productRepository
     * @param ProductHelper $productHelper
     */
    public function __construct(
        AmountService $amountService,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper
    ) {
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->amountService = $amountService;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function get(CartInterface $quote): array
    {
        $productsData = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = [];

            $product['id'] = (int)$item->getProduct()->getId();
            if ($option = $item->getOptionByCode('simple_product')) {
                $product['variantId'] = (string)$option->getProduct()->getId();
                $productModel = $this->productRepository->get($option->getProduct()->getSku());
            } else {
                $product['variantId'] = null;
                $productModel = $this->productRepository->getById($item->getProductId());

            }
            $product['price'] = $this->amountService->convertFloatToInteger(
                $item->getPrice() - ($item->getDiscountAmount() / $item->getQty()));
            $product['regularPrice'] = $this->amountService->convertFloatToInteger($item->getPrice());
            $product['totalPrice'] = $this->amountService->convertFloatToInteger($item->getRowTotal() - $item->getDiscountAmount());
            $product['name'] = $item->getName();
            $product['quantity'] = (int)$item->getQty();
            $product['images'] = [
                'fullUrl' => $this->productHelper->getImageUrl($productModel),
                'thumbnailUrl' => $this->productHelper->getThumbnailUrl($productModel)
            ];

            $product['attributes'] = [];

            $productsData[] = $product;
        }

        return $productsData;
    }
}
