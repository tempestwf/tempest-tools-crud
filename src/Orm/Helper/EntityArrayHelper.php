<?php
namespace TempestTools\Crud\Orm\Helper;


use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Crud\Constants\EntityEventsConstants;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Helper\EntityArrayHelperContract;
use TempestTools\Crud\Exceptions\EntityArrayHelperException;

class EntityArrayHelper extends ArrayHelper implements EntityArrayHelperContract{
    use AccessorMethodNameTrait;

    /**
     * @var EntityContract $entity
     */
    protected $entity;


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

    /**
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     * @throws EntityArrayHelperException
     */
    public function canChain (string $associationName, string $chainType, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'chainType'=> $chainType, 'self'=>$this];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $associationName]);

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getEntity()->getArrayHelper()->parse($fieldSettings, $extra);

        $allowed = $this->getEntity()->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getEntity()->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::chainTypeNotAllow($chainType, $associationName);
        }

        return $allowed;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $associationName
     * @param string $assignType
     * @param array $fieldSettings
     * @param bool $nosey
     * @return bool
     * @throws RuntimeException
     * @throws EntityArrayHelperException
     */
    public function canAssign (string $associationName, string $assignType=null, array $fieldSettings = NULL, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'assignType'=> $assignType, 'self'=>$this];
        $assignType = $assignType ?? 'null';
        if (!in_array($assignType, ['set', 'add', 'remove', 'setSingle', 'addSingle', 'removeSingle', 'null'], true)) {
            throw EntityArrayHelperException::assignTypeMustBe($assignType);
        }

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($associationName), $extra);


        $allowed = $this->getEntity()->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getEntity()->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::assignTypeNotAllow($assignType, $associationName);
        }
        return $allowed;
    }

    /**
     * @param bool $nosey
     * @return bool
     * @throws RuntimeException
     * @throws EntityArrayHelperException
     */
    public function allowed ($nosey = true):bool {

        /** @noinspection NullPointerExceptionInspection */
        $array = $this->getArray();
        $allowed = $array['allowed'] ?? true;

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getEntity()->getArrayHelper()->parse($allowed, ['self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::actionNotAllow();
        }
        return $allowed;
    }

    /**
     * @param string $fieldName
     * @return bool
     * @throws RuntimeException
     */
    public function checkFastMode(string $fieldName):bool
    {
        $extra = ['fieldName'=>$fieldName, 'self'=>$this];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName]);
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getEntity()->getArrayHelper()->parse($fieldSettings, $extra);

        $fastMode = $this->getEntity()->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->getEntity()->getArrayHelper()->parse($fastMode, $extra);
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
     * @param array $params
     * @return mixed
     * @throws RuntimeException
     */
    public function processSetField (array $params) {
        $fieldName = $params['fieldName'];
        $value = $params['value'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

        /** @noinspection NullPointerExceptionInspection */
        $this->canAssign($fieldName, 'set', $fieldSettings);
        $this->enforceField($fieldName, $value, $params, $fieldSettings);
        $this->closureOnField($fieldName, $params, $fieldSettings);
        $value = $this->setToOnField($fieldName, $params, $value, $fieldSettings);
        $value = $this->mutateOnField($fieldName, $params, $value, $fieldSettings);
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @throws RuntimeException
     * @throws EntityArrayHelperException
     */
    public function enforceRelation(string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

        // Check if fields that are needed to be enforced as enforced
        /** @noinspection NullPointerExceptionInspection */
        $enforce = isset($fieldSettings['enforce']) ? $this->getEntity()->getArrayHelper()->parse($fieldSettings['enforce'], $params) : [];

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getEntity()->getArrayHelper()->testEnforceValues($values, $enforce, $params);

        if ($allowed === false && $nosey === true) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $associationName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @throws RuntimeException
     * @return array
     */
    public function setToOnAssociation(string $associationName, array $values, array $params = [], array $fieldSettings=NULL):array {
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

        // Figure out if there are values that need to be set to, and set it to those values if any found
        /** @noinspection NullPointerExceptionInspection */
        $setTo = isset($fieldSettings['setTo']) ? $this->getEntity()->getArrayHelper()->parse($fieldSettings['setTo'], $params) : [];

        if ($setTo !== null) {
            $values = array_replace_recursive($values, $setTo);
        }
        return $values;
    }

    /**
     * @param array $params
     * @throws RuntimeException
     * @return array
     */
    protected function processAssociationParamsCore(array $params):array {
        $associationName = $params['associationName'];
        $values = $params['values'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

        // Check if assignment and chaining settings are allowed
        $chainType = $values['chainType'] ?? null;
        if ($chainType !== null) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canChain($associationName, $chainType);
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->enforceRelation($associationName, $values, $params, $fieldSettings);
        $this->closureOnField($associationName, $params, $fieldSettings);
        $values = $this->setToOnAssociation($associationName, $values, $params, $fieldSettings);
        $values = $this->mutateOnField($associationName, $params, $values, $fieldSettings);
        return $values;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws RuntimeException
     */
    public function mutateOnField (string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['mutate']) ? $this->getEntity()->getArrayHelper()->parse($fieldSettings['mutate'], $params) : $value;
        return $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     * @throws RuntimeException
     */
    public function setToOnField (string $fieldName, $params, $value, array $fieldSettings = NULL) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['setTo']) ? $this->getEntity()->getArrayHelper()->parse($fieldSettings['setTo'], $params) : $value;
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws RuntimeException
     * @throws EntityArrayHelperException
     */
    public function closureOnField (string $fieldName, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['closure']) && $this->getEntity()->getArrayHelper()->parse($fieldSettings['closure'], $params) === false);

        if ($allowed === false && $noisy === true) {
            throw EntityArrayHelperException::closureFails($fieldName);
        }
        return $allowed;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws RuntimeException
     * @throws EntityArrayHelperException
     */
    public function enforceField (string $fieldName, $value, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getEntity()->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        // Additional validation
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['enforce']) && $this->getEntity()->getArrayHelper()->parse($value, $params) !== $this->getEntity()->getArrayHelper()->parse($fieldSettings['enforce'], $params));

        // Any validation failure error out
        if ($allowed === false && $noisy === true) {
            throw EntityArrayHelperException::enforcementFails($fieldName);
        }

        return $allowed;
    }

    /**
     * @throws \RuntimeException
     */
    protected function processPrePersist() {
        $array = $this->getArray();

        if (isset($array['setTo'])) {
            $this->prePersistSetTo($array['setTo']);
        }

        if (isset($array['enforce'])) {
            $this->prePersistEnforce($array['enforce']);
        }

        if (isset($array['closure'])) {
            $this->prePersistClosure($array['closure']);
        }

        if (isset($array['mutate'])) {
            $this->prePersistMutate($array['mutate']);
        }

        if (isset($array['validate'])) {
            $this->prePersistValidate($array['validate']);
        }
    }

    /**
     * @param array $validate
     * @throws \RuntimeException
     */
    protected function prePersistValidate(array $validate)
    {
        $entity = $this->getEntity();
        $extra = ['validate'=>$validate, 'entity'=>$entity];
        $fields = $validate['fields'] ?? array_keys($validate['rules']);
        /** @noinspection NullPointerExceptionInspection */
        $fields = $this->getEntity()->getArrayHelper()->parse($fields, $extra);
        $rules = $validate['rules'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $rules = $this->getEntity()->getArrayHelper()->parse($rules, $extra);
        $messages = $validate['messages'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $messages = $this->getEntity()->getArrayHelper()->parse($messages, $extra);
        $customAttributes = $validate['customAttributes'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $customAttributes = $this->getEntity()->getArrayHelper()->parse($customAttributes, $extra);
        $values = $entity->getValuesOfFields($fields);
        $entity->validate($values, $rules, $messages, $customAttributes);
    }

    /**
     * @param callable $closure
     */
    protected function prePersistMutate(Callable $closure)
    {
        $entity = $this->getEntity();
        /** @noinspection NullPointerExceptionInspection */
        $this->getEntity()->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
    }

    /**
     * @param callable $closure
     * @throws \RuntimeException
     * @throws EntityArrayHelperException
     */
    protected function prePersistClosure(Callable $closure)
    {
        $entity = $this->getEntity();
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getEntity()->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
        if ($allowed === false) {
            throw EntityArrayHelperException::closureFails();
        }
    }

    /**
     * @param array $values
     */
    protected function prePersistSetTo(array $values)
    {
        $entity = $this->getEntity();
        $extra = ['self' => $entity];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getEntity()->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('set', $key);
            $entity->$methodName($value);
        }
    }

    /**
     * @param array $values
     * @throws \RuntimeException
     * @throws EntityArrayHelperException
     */
    protected function prePersistEnforce(array $values)
    {
        $entity = $this->getEntity();
        $extra = ['self' => $entity];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getEntity()->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('get', $key);
            $result = $entity->$methodName();
            if (!is_scalar($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    /** @noinspection NullPointerExceptionInspection */
                    $value2 = $this->getEntity()->getArrayHelper()->parse($value2, $extra);
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
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->checkFastMode($fieldName);
        if ($fastMode !== true) {
            $params = ['fieldName' => $fieldName, 'value' => $value, 'configArrayHelper' => $this, 'self' => $this->getEntity()];
            $eventArgs = $this->getEntity()->makeEventArgs($params);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $this->getEntity()->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_SET_FIELD, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $value = $this->processSetField($processedParams);

        }
        // All is ok so set it
        $setName = $this->accessorMethodName('set', $fieldName);
        $this->getEntity()->$setName($value);
    }

    /**
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(string $associationName, array $values): array
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->checkFastMode($associationName);
        if ($fastMode !== true) {

            $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $this, 'self' => $this->getEntity()];
            $eventArgs = $this->getEntity()->makeEventArgs($params);
            // Give event listeners a chance to do something and pull the args out again after wards
            /** @noinspection NullPointerExceptionInspection */
            $this->getEntity()->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            /** @noinspection NullPointerExceptionInspection */
            $values = $this->processAssociationParamsCore($processedParams);

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
    public function bindAssociation(string $assignType=null, string $associationName, EntityContract $entity = null, $force = false):void
    {
        if ($force === false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canAssign($associationName, $assignType);
        }

        if ($assignType !== null) {
            $methodName = $this->accessorMethodName($assignType, $associationName);
            $this->getEntity()->$methodName($entity);
        }
    }

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @throws \RuntimeException
     */
    public function ttPrePersist():void
    {
        $eventArgs = $this->getEntity()->makeEventArgs([]);

        // Give event listeners a chance to do something then pull out the args again
        /** @noinspection NullPointerExceptionInspection */
        $this->getEntity()->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_PERSIST, $eventArgs);

        /** @noinspection PhpParamsInspection */
        $this->processPrePersist();

        /** @noinspection NullPointerExceptionInspection */
        $this->getEntity()->getEventManager()->dispatchEvent(EntityEventsConstants::POST_PERSIST, $eventArgs);
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(array $fields = []): array
    {
        $result = [];
        foreach ($fields as $field) {
            $methodName = $this->accessorMethodName('get', $field);
            $value = $this->getEntity()->$methodName();
            $result[$field] = $value;
        }
        return $result;
    }

    /**
     * @return EntityContract
     */
    public function getEntity(): EntityContract
    {
        return $this->entity;
    }

    /**
     * @param EntityContract $entity
     */
    public function setEntity(EntityContract $entity):void
    {
        $this->entity = $entity;
    }
}
?>