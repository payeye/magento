<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PayeyeQuote extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('payeye_quote', 'entity_id');
    }

    /**
     * @param int $quoteId
     * @return string|null
     */
    public function getUuid(int $quoteId): ?string
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $field = $connection->quoteIdentifier(sprintf('%s.%s', $mainTable, 'cart_id'));

        $select = $connection->select()
            ->from($mainTable, ['uuid'])
            ->where($field . '=?', $quoteId);

        $result = $connection->fetchOne($select);

        return $result ?: null;
    }

    /**
     * @param string $uuid
     * @return int|null
     */
    public function getCartIdByUuid(string $uuid): ?int
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $field = $connection->quoteIdentifier(sprintf('%s.%s', $mainTable, 'uuid'));

        $select = $connection->select()
            ->from($mainTable, ['cart_id'])
            ->where($field . '=?', $uuid);
        $result = $connection->fetchOne($select);

        return (int)$result ?: null;
    }
}
