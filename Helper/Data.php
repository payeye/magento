<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

namespace PayEye\PayEye\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use PayEye\PayEye\Model\Config;

class Data extends AbstractHelper
{
    protected $config;

    public function __construct(
        Context $context,
        Config $config
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    public function getApiVersion()
    {
        return $this->config->getApiVersion();
    }

    public function getUiSide()
    {
        return $this->config->getUiSide();
    }

    public function getUiSideDistance()
    {
        return $this->config->getUiSideDistance();
    }

    public function getUiBottomDistance()
    {
        return $this->config->getUiBottomDistance();
    }

    public function getUiZIndex()
    {
        return $this->config->getUiZIndex();
    }
}
