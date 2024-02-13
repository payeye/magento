<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

class MagentoShippingMethodColumn extends Select
{
    /**
     * @var Config
     */
    private $shippingConfig;

    public function __construct(
        Context $context,
        Config $shippingConfig,
        array $data = []
    ) {
        $this->shippingConfig = $shippingConfig;
        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getActiveShippingMethods());
        }
        return parent::_toHtml();
    }

    private function getActiveShippingMethods(): array
    {
        $methods = [];
        $activeCarriers = $this->shippingConfig->getActiveCarriers();
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $carrierMethods = $carrierModel->getAllowedMethods();
            if ($carrierMethods) {
                $carrierTitle = $this->_scopeConfig->getValue(
                    'carriers/' . $carrierCode . '/title',
                    ScopeInterface::SCOPE_STORE
                );
                $methodsGroup = ['label' => $carrierTitle, 'value' => []];
                foreach ($carrierMethods as $methodCode => $methodName) {
                    $code = $carrierCode . '_' . $methodCode;
                    $methodsGroup['value'][] = ['value' => $code, 'label' => '[' . $carrierCode . '] ' . $methodName];
                }
                $methods[] = $methodsGroup;
            }
        }
        return $methods;
    }

    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }
}
