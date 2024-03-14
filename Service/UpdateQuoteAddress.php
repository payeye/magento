<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Service;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface as MagentoCartInterface;
use PayEye\Lib\Cart\CartRequestModel;
use PayEye\Lib\Model\Address;
use PayEye\Lib\Order\OrderCreateRequestModel;
use PayEye\Lib\Model\Billing;
use PayEye\Lib\Model\Shipping;
use PayEye\PayEye\Model\Config;
use PayEye\PayEye\Api\UpdateQuoteAddressInterface;
use Psr\Log\LoggerInterface;

class UpdateQuoteAddress implements UpdateQuoteAddressInterface
{
    private Config $config;
    private ShippingInformationInterface $shippingInformation;
    private AddressInterfaceFactory $address;
    private ShippingInformationManagementInterface $shippingInformationManagement;
    private LoggerInterface $logger;

    /**
     * @param Config $config
     * @param ShippingInformationInterface $shippingInformation
     * @param AddressInterfaceFactory $address
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        ShippingInformationInterface $shippingInformation,
        AddressInterfaceFactory $address,
        ShippingInformationManagementInterface $shippingInformationManagement,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->address = $address;
        $this->shippingInformation = $shippingInformation;
        $this->config = $config;
    }

    /**
     * @param MagentoCartInterface $quote
     * @param CartRequestModel|OrderCreateRequestModel $request
     * @return void
     */
    public function update(MagentoCartInterface $quote, CartRequestModel|OrderCreateRequestModel $request): void
    {
        if (!$request->getBilling() || !$request->getShipping() ||
            (!$request->getShippingProvider() && !$request->getShippingId())) {
            return;
        }

        $billingAddress = $this->createAddress($request->getBilling()->getAddress());
        $shippingAddress = $this->createAddress($request->getShipping()->getAddress());

        $billingAddress->setEmail($request->getBilling()->getEmail());
        $billingAddress->setTelephone($request->getBilling()->getPhoneNumber());
        $billingAddress->setFirstname($request->getBilling()->getFirstName());
        $billingAddress->setLastname($request->getBilling()->getLastName());

        if ($request instanceof OrderCreateRequestModel && $request->hasInvoice()) {
            $billingAddress = $this->createAddress($request->getInvoice()->getAddress());
            $billingAddress->setVatId($request->getInvoice()->getTaxId());
            $billingAddress->setCompany($request->getInvoice()->getCompanyName());
        }

        $shippingAddress->setEmail($request->getBilling()->getEmail());
        $shippingAddress->setTelephone($request->getBilling()->getPhoneNumber());
        $shippingAddress->setFirstname($request->getShipping()->getFirstName());
        $shippingAddress->setLastname($request->getShipping()->getLastName());

        $quote->getPayment()->setMethod('payeye');

        $this->shippingInformation->setBillingAddress($billingAddress);
        $this->shippingInformation->setShippingAddress($shippingAddress);
        if (!$request->getShippingId()) {
            $mappedShippingMethods = $this->config->getShippingMethods();
            foreach ($mappedShippingMethods as $magentoMethod => $payEyeMethod) {
                if ($payEyeMethod === $request->getShippingProvider()) {
                    $shippingMethod = explode('_', $magentoMethod);
                    $this->shippingInformation->setShippingCarrierCode($shippingMethod[0]);
                    $this->shippingInformation->setShippingMethodCode($shippingMethod[1]);
                    break;
                }
            }
        } else {
            $shippingMethod = explode('_', $request->getShippingId());
            $this->shippingInformation->setShippingCarrierCode($shippingMethod[0]);
            $this->shippingInformation->setShippingMethodCode($shippingMethod[1]);
        }

        try {
            $this->shippingInformationManagement->saveAddressInformation($quote->getId(), $this->shippingInformation);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param Address $address
     * @return AddressInterface
     */
    private function createAddress(Address $requestAddress): AddressInterface
    {
        $address = $this->address->create();

        $address->setCity($requestAddress->getCity());
        $address->setCountryId($requestAddress->getCountry());
        $address->setPostcode($requestAddress->getPostCode());
        $address->setStreet($requestAddress->getStreet() . ' ' .
            $requestAddress->getBuildingNumber() . ' ' .
            $requestAddress->getFlatNumber()
        );

        return $address;
    }
}
