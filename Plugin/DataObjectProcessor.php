<?php
/**
 * Copyright © PayEye sp. z o.o. All rights reserved.
 */

declare(strict_types=1);

namespace PayEye\PayEye\Plugin;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Reflection\DataObjectProcessor as MagentoDataObjectProcessor;

class DataObjectProcessor
{
    /**
     * @param MagentoDataObjectProcessor $subject
     * @param array $result
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function afterBuildOutputDataArray(
        MagentoDataObjectProcessor $subject,
        array $result,
        $dataObject,
        $dataObjectType
    ) {
        if (!str_contains($dataObjectType, 'PayEye')) {
            return $result;
        }

        $result = $this->changetoCamelCaseKeys($result);
        return $result;
    }

    /**
     * @param $array
     * @return array
     */
    private function changetoCamelCaseKeys($array) {
        $camelCaseArray = [];
        foreach ($array as $key => $val) {
            $camelCaseArray[SimpleDataObjectConverter::snakeCaseToCamelCase($key)] = $val;
        }
        return $camelCaseArray;
    }
}
