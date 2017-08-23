<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Crud\Orm\Builder;

use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Crud\Orm\Utility\BadBuilderCallTrait;

class PrePersistEntityBuilder implements PrePersistEntityBuilderContract
{

    use BadBuilderCallTrait, AccessorMethodNameTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param array $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
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
                        throw EntityArrayHelperException::enforcementFails();
                    }
                }
            } else if ($result !== $value) {
                throw EntityArrayHelperException::enforcementFails();
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param \Closure $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function closure(EntityContract $entity, \Closure $fieldSetting):void
    {
        /** @noinspection NullPointerExceptionInspection */
        if ($entity->getArrayHelper()->parseClosure($fieldSetting, ['self' => $entity]) === false) {
            throw EntityArrayHelperException::closureFails();
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param mixed $fieldSetting
     * @return mixed
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
     * @param EntityContract $entity
     * @param mixed $fieldSetting
     * @return mixed
     */
    public function mutate (EntityContract $entity, $fieldSetting):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $entity->getArrayHelper()->parseClosure($fieldSetting, ['self' => $entity]);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
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
        $values = $entity->getValuesOfFields($fields);
        $entity->validate($values, $rules, $messages, $customAttributes);
    }

}