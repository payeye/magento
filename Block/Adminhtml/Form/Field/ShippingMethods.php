<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;

class ShippingMethods extends AbstractFieldArray
{
    /** @var AbstractBlock */
    private $magentoShippingMethodRenderer;
    /** @var AbstractBlock */
    private $payeyeShippingMethodRenderer;

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('magento_shipping_method', [
            'label' => __('Magento shipping method'),
            'class' => 'required-entry',
            'renderer' => $this->getMagentoShippingMethodRenderer(),
        ]);

        $this->addColumn('payeye_shipping_method', [
            'label' => __('PayEye shipping method'),
            'class' => 'required-entry',
            'renderer' => $this->getPayeyeShippingMethodRenderer(),
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $magentoShippingMethod = $row->getMagentoShippingMethod();
        if ($magentoShippingMethod !== null) {
            $options['option_' . $this->getMagentoShippingMethodRenderer()->calcOptionHash($magentoShippingMethod)]
                = 'selected="selected"';
        }
        $payeyeShippingMethod = $row->getPayeyeShippingMethod();
        if ($payeyeShippingMethod !== null) {
            $options['option_' . $this->getPayeyeShippingMethodRenderer()->calcOptionHash($payeyeShippingMethod)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    private function getMagentoShippingMethodRenderer(): AbstractBlock
    {
        if (!$this->magentoShippingMethodRenderer) {
            $this->magentoShippingMethodRenderer = $this->getRenderer(MagentoShippingMethodColumn::class);
        }

        return  $this->magentoShippingMethodRenderer;
    }

    private function getPayeyeShippingMethodRenderer(): AbstractBlock
    {
        if (!$this->payeyeShippingMethodRenderer) {
            $this->payeyeShippingMethodRenderer = $this->getRenderer(PayeyeShippingMethodColumn::class);
        }

        return $this->payeyeShippingMethodRenderer;
    }

    /**
     * @param string $className
     * @return BlockInterface|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRenderer(string $className): AbstractBlock
    {
        return $this->getLayout()->createBlock(
            $className,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
}
