<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Scribe\Orm\Builder;

use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Scribe\Contracts\Orm\Builder\PrePersistEntityBuilderContract;
use TempestTools\Scribe\Contracts\Orm\EntityContract;
use TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Scribe\Orm\Utility\BadBuilderCallTrait;

/**
 * A builder that takes data store on an array, verifies it and modifies it as needed. This is used when processing data from an entity during the pre-persist event.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class PrePersistEntityBuilder implements PrePersistEntityBuilderContract
{

    use BadBuilderCallTrait, AccessorMethodNameTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * Enforces the fields of the entity match with the values specified in the config
     * @param EntityContract $entity
     * @param array $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function enforce(EntityContract $entity, array $fieldSetting):void
    {
        $arrayHelper = $entity->getArrayHelper();
        $extra = ['self' => $entity];
        foreach ($fieldSetting as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $arrayHelper->parse($value, $extra);
            $methodName = $this->accessorMethodName('get', $key);
            $result = $entity->$methodName();
            if (!is_scalar($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    /** @noinspection NullPointerExceptionInspection */
                    $value2 = $arrayHelper->parse($value2, $extra);
                    $methodName = $this->accessorMethodName('get', $key2);
                    $result2 = $result->$methodName();
                    if ($result2 !== $value2) {
                        throw EntityArrayHelperException::enforcementFails($key2);
                    }
                }
            } else if ($result !== $value) {
                throw EntityArrayHelperException::enforcementFails();
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Uses a closure to verify that the entity is valid
     * @param EntityContract $entity
     * @param \Closure $fieldSetting
     * @return mixed
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function closure(EntityContract $entity, \Closure $fieldSetting):void
    {
        /** @noinspection NullPointerExceptionInspection */
        if ($entity->getArrayHelper()->parse($fieldSetting, ['self' => $entity]) === false) {
            throw EntityArrayHelperException::closureFails();
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Sets fields of the entity to match with values stored in the config
     * @param EntityContract $entity
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function setTo (EntityContract $entity, $fieldSetting):void
    {
        $extra = ['self' => $entity];
        $arrayHelper = $entity->getArrayHelper();
        /** @var array[] $fieldSetting*/
        foreach ($fieldSetting as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $arrayHelper->parse($value, $extra);
            $methodName = $this->accessorMethodName('set', $key);
            $entity->$methodName($value);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Modifies the entity using a closure stored in the config
     * @param EntityContract $entity
     * @param mixed $fieldSetting
     * @return mixed
     */
    public function mutate (EntityContract $entity, $fieldSetting):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $entity->getArrayHelper()->parse($fieldSetting, ['self' => $entity]);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Validates the entity based on the validation data stored in the config. It does this by leveraging a validation factory.
     * @param EntityContract $entity
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \RuntimeException
     */
    public function validate (EntityContract $entity, $fieldSetting):void
    {
        $extra = ['validate'=>$fieldSetting, 'entity'=>$entity];
        $arrayHelper = $entity->getArrayHelper();
        $fields = $fieldSetting['fields'] ?? array_keys($fieldSetting['rules']);
        /** @noinspection NullPointerExceptionInspection */
        $fields = $arrayHelper->parse($fields, $extra);
        $rules = $fieldSetting['rules'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $rules = $arrayHelper->parse($rules, $extra);
        $messages = $fieldSetting['messages'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $messages = $arrayHelper->parse($messages, $extra);
        $customAttributes = $fieldSetting['customAttributes'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $customAttributes = $arrayHelper->parse($customAttributes, $extra);
        $values = $entity->getBindParams();
        $entity->validate($values, $rules, $messages, $customAttributes);
    }

}