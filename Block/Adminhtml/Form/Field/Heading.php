<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Heading extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<tr id="row_' . $element->getHtmlId() . '">';
        $html .= ' <td class="label"></td>';
        $html .= ' <td class="value">';
        $html .=  $element->getComment();
        $html .= ' </td>';
        $html .= ' <td></td>';
        $html .= '</tr>';

        return $html;
    }
}
