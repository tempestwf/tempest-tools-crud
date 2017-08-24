<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Crud\Orm\Builder;


use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToFieldPropertyBuilderContract;
use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Crud\Orm\Utility\BadBuilderCallTrait;

class ArrayToFieldPropertyBuilder implements ArrayToFieldPropertyBuilderContract
{

    use BadBuilderCallTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function enforce(ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting)
    {
        // Any validation failure error out
        if ($value !== $arrayHelper->parse($fieldSetting, $params)) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }

        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     * @internal param EntityContract $entity
     */
    public function closure(ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting)
    {
        if ($arrayHelper->parse($fieldSetting, $params) === false ) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param mixed $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function setTo (ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting)
    {
        return $arrayHelper->parse($fieldSetting, $params);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function mutate (ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting) {
        return $arrayHelper->parse($fieldSetting, $params);
    }

}