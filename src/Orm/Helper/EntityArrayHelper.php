<?php
namespace TempestTools\Crud\Orm\Helper;


use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Crud\Constants\EntityEventsConstants;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Helper\EntityArrayHelperContract;
use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;

class EntityArrayHelper extends ArrayHelper implements EntityArrayHelperContract{
    use AccessorMethodNameTrait;

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     * @throws RuntimeException
     */
    public function getConfigForField(string $fieldName, string $keyName)
    {
        return $this->parseArrayPath(['fields', $fieldName, $keyName]);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain (EntityContract $entity, string $associationName, string $chainType, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'chainType'=> $chainType, 'self'=>$this];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $associationName]);

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $entity->getArrayHelper()->parse($fieldSettings, $extra);

        $allowed = $entity->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::chainTypeNotAllow($chainType, $associationName);
        }

        return $allowed;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $assignType
     * @param array $fieldSettings
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function canAssign (EntityContract $entity, string $associationName, string $assignType=null, array $fieldSettings = NULL, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'assignType'=> $assignType, 'self'=>$this];
        $assignType = $assignType ?? 'null';
        if (!in_array($assignType, ['set', 'add', 'remove', 'setSingle', 'addSingle', 'removeSingle', 'null'], true)) {
            throw EntityArrayHelperException::assignTypeMustBe($assignType);
        }

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($associationName), $extra);


        $allowed = $entity->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::assignTypeNotAllow($assignType, $associationName);
        }
        return $allowed;
    }

    /**
     * @param EntityContract $entity
     * @param bool $nosey
     * @return bool
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function allowed (EntityContract $entity, $nosey = true):bool {

        /** @noinspection NullPointerExceptionInspection */
        $array = $this->getArray();
        $allowed = $array['allowed'] ?? true;

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse($allowed, ['self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::actionNotAllow();
        }
        return $allowed;
    }

    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @return bool
     * @throws \RuntimeException
     */
    public function checkFastMode(EntityContract $entity, string $fieldName):bool
    {
        $extra = ['fieldName'=>$fieldName, 'self'=>$this];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName]);
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $entity->getArrayHelper()->parse($fieldSettings, $extra);

        $fastMode = $entity->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $entity->getArrayHelper()->parse($fastMode, $extra);
        return $fastMode;
    }

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws RuntimeException
     */
    public function getFieldSettings (string $fieldName, array $params = []):array {
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName], $params);
        $fieldSettings = $fieldSettings??[];
        return $fieldSettings;
    }

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField (EntityContract $entity, array $params) {
        $fieldName = $params['fieldName'];
        $value = $params['value'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

        /** @noinspection NullPointerExceptionInspection */
        $this->canAssign($entity, $fieldName, 'set', $fieldSettings);
        $this->enforceField($entity, $fieldName, $value, $params, $fieldSettings);
        $this->closureOnField($entity, $fieldName, $params, $fieldSettings);
        $value = $this->setToOnField($entity, $fieldName, $params, $value, $fieldSettings);
        $value = $this->mutateOnField($entity, $fieldName, $params, $value, $fieldSettings);
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @throws \RuntimeException
     */
    public function enforceRelation(EntityContract $entity, string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

        // Check if fields that are needed to be enforced as enforced
        /** @noinspection NullPointerExceptionInspection */
        $enforce = isset($fieldSettings['enforce']) ? $entity->getArrayHelper()->parse($fieldSettings['enforce'], $params) : [];

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->testEnforceValues($values, $enforce, $params);

        if ($allowed === false && $nosey === true) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @return array
     * @throws \RuntimeException
     */
    public function setToOnAssociation(EntityContract $entity, string $associationName, array $values, array $params = [], array $fieldSettings=NULL):array {
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

        // Figure out if there are values that need to be set to, and set it to those values if any found
        /** @noinspection NullPointerExceptionInspection */
        $setTo = isset($fieldSettings['setTo']) ? $entity->getArrayHelper()->parse($fieldSettings['setTo'], $params) : [];

        if ($setTo !== null) {
            $values = array_replace_recursive($values, $setTo);
        }
        return $values;
    }

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return array
     * @throws \RuntimeException
     */
    protected function processAssociationParamsCore(EntityContract $entity, array $params):array {
        $associationName = $params['associationName'];
        $values = $params['values'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $entity->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

        // Check if assignment and chaining settings are allowed
        $chainType = $values['chainType'] ?? null;
        if ($chainType !== null) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canChain($entity, $associationName, $chainType);
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->enforceRelation($entity, $associationName, $values, $params, $fieldSettings);
        $this->closureOnField($entity, $associationName, $params, $fieldSettings);
        $values = $this->setToOnAssociation($entity, $associationName, $values, $params, $fieldSettings);
        $values = $this->mutateOnField($entity, $associationName, $params, $values, $fieldSettings);
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return mixed
     * @throws \RuntimeException
     */
    public function mutateOnField (EntityContract $entity, string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['mutate']) ? $entity->getArrayHelper()->parse($fieldSettings['mutate'], $params) : $value;
        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws \RuntimeException
     */
    public function setToOnField (EntityContract $entity, string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['setTo']) ? $entity->getArrayHelper()->parse($fieldSettings['setTo'], $params) : $value;
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function closureOnField (EntityContract $entity, string $fieldName, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['closure']) && $entity->getArrayHelper()->parse($fieldSettings['closure'], $params) === false);

        if ($allowed === false && $noisy === true) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $allowed;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function enforceField (EntityContract $entity, string $fieldName, $value, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $entity->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        // Additional validation
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['enforce']) && $entity->getArrayHelper()->parse($value, $params) !== $entity->getArrayHelper()->parse($fieldSettings['enforce'], $params));

        // Any validation failure error out
        if ($allowed === false && $noisy === true) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }

        return $allowed;
    }

    /**
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    protected function processPrePersist(EntityContract $entity) {
        $array = $this->getArray();

        if (isset($array['setTo'])) {
            $this->prePersistSetTo($entity, $array['setTo']);
        }

        if (isset($array['enforce'])) {
            $this->prePersistEnforce($entity, $array['enforce']);
        }

        if (isset($array['closure'])) {
            $this->prePersistClosure($entity, $array['closure']);
        }

        if (isset($array['mutate'])) {
            $this->prePersistMutate($entity, $array['mutate']);
        }

        if (isset($array['validate'])) {
            $this->prePersistValidate($entity, $array['validate']);
        }
    }

    /**
     * @param EntityContract $entity
     * @param array $validate
     * @throws \RuntimeException
     */
    protected function prePersistValidate(EntityContract $entity, array $validate)
    {
        $extra = ['validate'=>$validate, 'entity'=>$entity];
        $fields = $validate['fields'] ?? array_keys($validate['rules']);
        /** @noinspection NullPointerExceptionInspection */
        $fields = $entity->getArrayHelper()->parse($fields, $extra);
        $rules = $validate['rules'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $rules = $entity->getArrayHelper()->parse($rules, $extra);
        $messages = $validate['messages'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $messages = $entity->getArrayHelper()->parse($messages, $extra);
        $customAttributes = $validate['customAttributes'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $customAttributes = $entity->getArrayHelper()->parse($customAttributes, $extra);
        $values = $entity->getValuesOfFields($fields);
        $entity->validate($values, $rules, $messages, $customAttributes);
    }

    /**
     * @param EntityContract $entity
     * @param callable $closure
     */
    protected function prePersistMutate(EntityContract $entity, Callable $closure)
    {
        /** @noinspection NullPointerExceptionInspection */
        $entity->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
    }

    /**
     * @param EntityContract $entity
     * @param callable $closure
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    protected function prePersistClosure(EntityContract $entity, Callable $closure)
    {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
        if ($allowed === false) {
            throw EntityArrayHelperException::closureFails();
        }
    }

    /**
     * @param EntityContract $entity
     * @param array $values
     */
    protected function prePersistSetTo(EntityContract $entity, array $values)
    {
        $extra = ['self' => $entity];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $entity->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('set', $key);
            $entity->$methodName($value);
        }
    }

    /**
     * @param EntityContract $entity
     * @param array $values
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    protected function prePersistEnforce(EntityContract $entity, array $values)
    {
        $extra = ['self' => $entity];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $entity->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('get', $key);
            $result = $entity->$methodName();
            if (!is_scalar($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    /** @noinspection NullPointerExceptionInspection */
                    $value2 = $entity->getArrayHelper()->parse($value2, $extra);
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


    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @throws \RuntimeException
     */
    public function setField(EntityContract $entity, string $fieldName, $value):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->checkFastMode($entity, $fieldName);
        if ($fastMode !== true) {
            $params = ['fieldName' => $fieldName, 'value' => $value, 'configArrayHelper' => $this, 'self' => $entity];
            $eventArgs = $entity->makeEventArgs($params);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $entity->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_SET_FIELD, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $value = $this->processSetField($entity, $processedParams);

        }
        // All is ok so set it
        $setName = $this->accessorMethodName('set', $fieldName);
        $entity->$setName($value);
    }

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(EntityContract $entity, string $associationName, array $values): array
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->checkFastMode($entity, $associationName);
        if ($fastMode !== true) {

            $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $this, 'self' => $entity];
            $eventArgs = $entity->makeEventArgs($params);
            // Give event listeners a chance to do something and pull the args out again after wards
            /** @noinspection NullPointerExceptionInspection */
            $entity->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            /** @noinspection NullPointerExceptionInspection */
            $values = $this->processAssociationParamsCore($entity, $processedParams);

        }
        return $values;

    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(EntityContract $entity, string $assignType=null, string $associationName, $force = false):void
    {
        if ($force === false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canAssign($entity, $assignType);
        }

        if ($assignType !== null) {
            $methodName = $this->accessorMethodName($assignType, $associationName);
            $entity->$methodName($entity);
        }
    }

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    public function ttPrePersist(EntityContract $entity):void
    {
        $eventArgs = $entity->makeEventArgs([]);

        // Give event listeners a chance to do something then pull out the args again
        /** @noinspection NullPointerExceptionInspection */
        $entity->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_PERSIST, $eventArgs);

        /** @noinspection PhpParamsInspection */
        $this->processPrePersist($entity);

        /** @noinspection NullPointerExceptionInspection */
        $entity->getEventManager()->dispatchEvent(EntityEventsConstants::POST_PERSIST, $eventArgs);
    }

    /**
     * @param EntityContract $entity
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(EntityContract $entity, array $fields = []): array
    {
        $result = [];
        foreach ($fields as $field) {
            $methodName = $this->accessorMethodName('get', $field);
            $value = $entity->$methodName();
            $result[$field] = $value;
        }
        return $result;
    }

}
?>