<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Plugin\Validator;

use Magento\Customer\Model\Address\Validator\Country as Subject;
use Magento\Directory\Model\AllowedCountries;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Zend_Validate;

class OptionalRegionValidator
{
    private CartRepositoryInterface $cartRepository;
    private AllowedCountries $allowedCountriesReader;
    private Escaper $escaper;
    private LoggerInterface $logger;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AllowedCountries $allowedCountriesReader
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AllowedCountries $allowedCountriesReader,
        Escaper $escaper,
        LoggerInterface $logger,
        ProductMetadataInterface $productMetadata
    ) {
        $this->logger = $logger;
        $this->escaper = $escaper;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->cartRepository = $cartRepository;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @param AbstractAddress $address
     * @return array
     * @throws ValidateException
     */
    public function afterValidate(Subject $subject, array $result, AbstractAddress $address): array
    {
        if (!empty($result) && $this->isOptionalRegionApplicableForQuoteAddress($address)) {
            $result = $this->validateCountry($address);
        }

        return $result;
    }

    private function isOptionalRegionApplicableForQuoteAddress(AbstractAddress $address): bool
    {
        try {
            $quote = $this->cartRepository->get((int)$address->getData(OrderInterface::QUOTE_ID));
            $extensionAttributes = $quote->getExtensionAttributes();
            return (bool)$extensionAttributes->getIsPayeye();
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * @param AbstractAddress $address
     * @return array
     * @throws ValidateException
     */
    private function validateCountry(AbstractAddress $address): array
    {
        $countryId = $address->getCountryId();
        $errors = [];

        if (version_compare($this->productMetadata->getVersion(), '2.4.6', '<')) {
            $if = Zend_Validate::is($countryId, NotEmpty::class);
        } else {
            $if = ValidatorChain::is($countryId, NotEmpty::class);
        }

        if (!$if) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'countryId']);
        } elseif (!in_array($countryId, $this->getWebsiteAllowedCountries($address), true)) {
            $errors[] = __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'countryId', 'value' => $this->escaper->escapeHtml($countryId)]
            );
        }

        return $errors;
    }

    /**
     * @param AbstractAddress $address
     * @return array
     */
    private function getWebsiteAllowedCountries(AbstractAddress $address): array
    {
        $storeId = $address->getData('store_id');

        return $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);
    }
}
