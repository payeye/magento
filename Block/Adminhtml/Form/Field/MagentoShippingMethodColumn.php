<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use Magento\Shipping\Model\Config\Source\AllMethods;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
class MagentoShippingMethodColumn extends Select
{
    /**
     * @var AllMethods
     */
    private $allMethods;

    public function __construct(
        Context $context,
        AllMethods $allMethods,
        array $data = []
    ) {
        $this->allMethods = $allMethods;
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
            $this->setOptions($this->allMethods->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): MagentoShippingMethodColumn
    {
        return $this->setName($value);
    }
}
