<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PayEye\PayEye\Model\ResourceModel\PayeyeQuote as PayeyeQuoteResourceModel;

/**
 * PayeyeQuoteUuid model
 *
 * @method string getUuid()
 * @method PayeyeQuote setUuid(string $id)
 * @method bool getOpen()
 * @method PayeyeQuote setOpen(bool $open)
 * @method int getCartId()
 * @method PayeyeQuote setCartId(int $cartId)
 */
class PayeyeQuote extends AbstractModel
{
    private Random $randomDataGenerator;

    public function __construct(
        Context $context,
        Registry $registry,
        Random $randomDataGenerator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->randomDataGenerator = $randomDataGenerator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(PayeyeQuoteResourceModel::class);
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if (empty($this->getUuid())) {
            $this->setUuid($this->randomDataGenerator->getUniqueHash());
        }

        return $this;
    }
}
