<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Crud\Orm\Builder;


use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToAssociationPropertyBuilderContract;
use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Crud\Orm\Utility\BadBuilderCallTrait;

class ArrayToAssociationPropertyBuilder implements ArrayToAssociationPropertyBuilderContract
{
    use BadBuilderCallTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function enforce(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting)
    {
        // Check if fields that are needed to be enforced as enforced
        /** @noinspection NullPointerExceptionInspection */
        $enforce = $arrayHelper->parse($fieldSetting, $params);
        if ($arrayHelper->testEnforceValues($values, $enforce, $params) === false) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     * @internal param EntityContract $entity
     */
    public function closure(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting)
    {
        if ($arrayHelper->parse($fieldSetting, $params) === false ) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $params
     * @param array $values
     * @param mixed $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function setTo (ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting)
    {
        $setTo = $arrayHelper->parse($fieldSetting, $params);
        $values = array_replace_recursive($values, $setTo);
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function mutate (ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting) {
        return $arrayHelper->parse($fieldSetting, $params);
    }

}