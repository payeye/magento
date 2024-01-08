<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use PayEye\PayEye\Model\Config\Source\PayeyeShippingMethod;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
class PayeyeShippingMethodColumn extends Select
{
    private PayeyeShippingMethod $payeyeShippingMethod;

    public function __construct(
        Context $context,
        PayeyeShippingMethod $payeyeShippingMethod,
        array $data = []
    ) {
        $this->payeyeShippingMethod = $payeyeShippingMethod;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->payeyeShippingMethod->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): PayeyeShippingMethodColumn
    {
        return $this->setName($value);
    }
}
