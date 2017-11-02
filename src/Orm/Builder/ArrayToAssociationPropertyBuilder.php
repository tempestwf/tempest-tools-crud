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

/**
 * A builder that takes data store on an array, verifies it and modifies it as needed. This is used when processing data that will be used in regards to an entity association.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class ArrayToAssociationPropertyBuilder implements ArrayToAssociationPropertyBuilderContract
{
    use BadBuilderCallTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * Makes sure that the values for the association match with the values that are set to be enforced in the entity config
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
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
     * Runs a closure to validate the values being set for the association
     *
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function closure(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting)
    {
        if ($arrayHelper->parse($fieldSetting, $params) === false ) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Sets values being set on an association to make the values stored in a setTo array in the config.
     *
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $params
     * @param array $values
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function setTo (ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting)
    {
        $setTo = $arrayHelper->parse($fieldSetting, $params);
        $values = array_replace_recursive($values, $setTo);
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Mutates the values being set on an association using a closure.
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function mutate (ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting) {
        return $arrayHelper->parse($fieldSetting, $params);
    }

}