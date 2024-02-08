<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class MagentoShippingMethodColumn extends Select
{
    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        Config $shippingConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getActiveShippingMethods());
        }
        return parent::_toHtml();
    }

    private function getActiveShippingMethods()
    {
        $activeMethods = [];
        $allMethods = $this->shippingConfig->getActiveCarriers($this->storeManager->getStore()->getId());
        foreach ($allMethods as $carrierCode => $carrierModel) {
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $methodName) {
                    $code = $carrierCode . '_' . $methodCode;
                    $label = sprintf('[%s] %s', $carrierCode, $methodName);
                    $activeMethods[] = ['value' => $code, 'label' => $label];
                }
            }
        }
        return $activeMethods;
    }
}
