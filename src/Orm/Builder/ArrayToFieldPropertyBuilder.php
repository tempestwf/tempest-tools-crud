<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Scribe\Orm\Builder;


use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Scribe\Contracts\Orm\Builder\ArrayToFieldPropertyBuilderContract;
use TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Scribe\Orm\Utility\BadBuilderCallTrait;

/**
 * A builder that takes data store on an array, verifies it and modifies it as needed. This is used when processing data that will be used in regards to an entity field that isn't an association.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class ArrayToFieldPropertyBuilder implements ArrayToFieldPropertyBuilderContract
{

    use BadBuilderCallTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * Enforces that a field value matches with the config
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException
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
     * Uses a closure to validate a value being set on a field.
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function closure(ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting)
    {
        if ($arrayHelper->parse($fieldSetting, $params) === false ) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Changes a value that is about to be set on a field to match with the value stored in the config.
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function setTo (ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting)
    {
        return $arrayHelper->parse($fieldSetting, $params);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Mutates a value about to be set on a field using a closure.
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function mutate (ArrayHelperContract $arrayHelper, string $fieldName, $value, array $params, $fieldSetting) {
        return $arrayHelper->parse($fieldSetting, $params);
    }

}