<?php
namespace TempestTools\Crud\Orm\Helper;


use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Contracts\EntityHelperContract as EntityContract;
use TempestTools\Crud\Contracts\EntityArrayHelperContract as EntityArrayHelperContract;

class EntityHelper extends ArrayHelper implements EntityArrayHelperContract{
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;

    const ERRORS = [
        'chainTypeNotAllow'=>[
            'message'=>'Error: Requested chain type not permitted. chainType = %s, relationName = %s.',
        ],
        'assignTypeNotAllow'=>[
            'message'=>'Error: Requested assign type not permitted. assignType = %s, fieldName = %s.',
        ],
        'actionNotAllow'=>[
            'message'=>'Error: the requested action is not allowed on this entity for this request.'
        ],
        'enforcementFails' => [
            'message' => 'Error: A field is not set to it\'s enforced value. fieldName = %s.',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass. fieldName = %s.',
        ],
        'assignTypeMustBe' => [
            'message' => 'Error: Assign type must be set, add or remove. assignType = %s',
        ],
    ];

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
     * @throws RuntimeException
     */
    public function canChain (string $associationName, string $chainType, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'chainType'=> $chainType, 'self'=>$this];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->parseArrayPath(['fields', $associationName]);

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getArrayHelper()->parse($fieldSettings, $extra);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('chainTypeNotAllow')['message'], $chainType, $associationName));
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
     */
    public function canAssign (string $associationName, string $assignType=null, array $fieldSettings = NULL, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'assignType'=> $assignType, 'self'=>$this];
        $assignType = $assignType ?? 'null';
        if (!in_array($assignType, ['set', 'add', 'remove', 'setSingle', 'addSingle', 'removeSingle', 'null'], true)) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('assignTypeMustBe')['message'], $assignType));
        }

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($associationName), $extra);


        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($nosey === true && $allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('assignTypeNotAllow')['message'], $assignType, $associationName));
        }
        return $allowed;
    }

    /**
     * @param bool $nosey
     * @return bool
     * @throws RuntimeException
     */
    public function allowed ($nosey = true):bool {

        /** @noinspection NullPointerExceptionInspection */
        $array = $this->getArray();
        $allowed = $array['allowed'] ?? true;

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, ['self'=>$this]);
        if ($nosey === true && $allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('actionNotAllow')['message']);
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
        $fieldSettings = $this->getArrayHelper()->parse($fieldSettings, $extra);

        $fastMode = $this->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->getArrayHelper()->parse($fastMode, $extra);
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
        $fieldSettings = $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

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
     */
    public function enforceRelation(string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true) {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);

        // Check if fields that are needed to be enforced as enforced
        /** @noinspection NullPointerExceptionInspection */
        $enforce = isset($fieldSettings['enforce']) ? $this->getArrayHelper()->parse($fieldSettings['enforce'], $params) : [];

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->testEnforceValues($values, $enforce, $params);

        if ($allowed === false && $nosey === true) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message'], $fieldName));
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
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

        // Figure out if there are values that need to be set to, and set it to those values if any found
        /** @noinspection NullPointerExceptionInspection */
        $setTo = isset($fieldSettings['setTo']) ? $this->getArrayHelper()->parse($fieldSettings['setTo'], $params) : [];

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
    public function processAssociationParams(array $params):array {
        $associationName = $params['associationName'];
        $values = $params['values'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getArrayHelper()->parse($this->getFieldSettings($associationName), $params);

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
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['mutate']) ? $this->getArrayHelper()->parse($fieldSettings['mutate'], $params) : $value;
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
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $value = isset($fieldSettings['setTo']) ? $this->getArrayHelper()->parse($fieldSettings['setTo'], $params) : $value;
        return $value;
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     * @throws RuntimeException
     */
    public function closureOnField (string $fieldName, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['closure']) && $this->getArrayHelper()->parse($fieldSettings['closure'], $params) === false);

        if ($allowed === false && $noisy === true) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('closureFails')['message'], $fieldName));
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
     */
    public function enforceField (string $fieldName, $value, array $params, array $fieldSettings = NULL, bool $noisy = true):bool {
        // Get the settings for the field so we can do quick comparisons
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getArrayHelper()->parse($this->getFieldSettings($fieldName), $params);
        // Additional validation
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($fieldSettings['enforce']) && $this->getArrayHelper()->parse($value, $params) !== $this->getArrayHelper()->parse($fieldSettings['enforce'], $params));

        // Any validation failure error out
        if ($allowed === false && $noisy === true) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message'], $fieldName));
        }

        return $allowed;
    }

    /**
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    public function processPrePersist(EntityContract $entity) {
        $array = $this->getArray();

        if (isset($array['setTo'])) {
            $this->prePersistSetTo($array['setTo'], $entity);
        }

        if (isset($array['enforce'])) {
            $this->prePersistEnforce($array['enforce'], $entity);
        }

        if (isset($array['closure'])) {
            $this->prePersistClosure($array['closure'], $entity);
        }

        if (isset($array['mutate'])) {
            $this->prePersistMutate($array['mutate'], $entity);
        }

        if (isset($array['validate'])) {
            $this->prePersistValidate($array['validate'], $entity);
        }
    }

    /**
     * @param array $validate
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    protected function prePersistValidate(array $validate, EntityContract $entity)
    {
        $extra = ['validate'=>$validate, 'entity'=>$entity];
        $fields = $validate['fields'] ?? array_keys($validate['rules']);
        /** @noinspection NullPointerExceptionInspection */
        $fields = $this->getArrayHelper()->parse($fields, $extra);
        $rules = $validate['rules'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $rules = $this->getArrayHelper()->parse($rules, $extra);
        $messages = $validate['messages'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $messages = $this->getArrayHelper()->parse($messages, $extra);
        $customAttributes = $validate['customAttributes'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $customAttributes = $this->getArrayHelper()->parse($customAttributes, $extra);
        $values = $entity->getValuesOfFields($fields);
        $entity->validate($values, $rules, $messages, $customAttributes);
    }

    /**
     * @param callable $closure
     * @param EntityContract $entity
     */
    protected function prePersistMutate(Callable $closure, EntityContract $entity)
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
    }

    /**
     * @param callable $closure
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    protected function prePersistClosure(Callable $closure, EntityContract $entity)
    {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parseClosure($closure, ['self' => $entity]);
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('closureFails')['message']);
        }
    }

    /**
     * @param array $values
     * @param EntityContract $entity
     */
    protected function prePersistSetTo(array $values, EntityContract $entity)
    {
        $extra = ['self' => $this];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getArrayHelper()->parse($value, $extra);
            $methodName = $entity->accessorMethodName('set', $key);
            $entity->$methodName($value);
        }
    }

    /**
     * @param array $values
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    protected function prePersistEnforce(array $values, EntityContract $entity)
    {
        $extra = ['self' => $entity];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getArrayHelper()->parse($value, $extra);
            $methodName = $entity->accessorMethodName('get', $key);
            $result = $entity->$methodName();
            if (!is_scalar($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    /** @noinspection NullPointerExceptionInspection */
                    $value2 = $this->getArrayHelper()->parse($value2, $extra);
                    $methodName = $entity->accessorMethodName('get', $key2);
                    $result2 = $result->$methodName();
                    if ($result2 !== $value2) {
                        throw new RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message'], $result2, $value2));
                    }
                }
            } else if ($result !== $value) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('enforcementFails')['message'], $result, $value));
            }
        }
    }
}
?>