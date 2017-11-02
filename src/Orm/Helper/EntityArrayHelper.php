<?php
namespace TempestTools\Crud\Orm\Helper;

use ArrayObject;
use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Crud\Constants\EntityEventsConstants;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToAssociationPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToFieldPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\PrePersistEntityBuilderContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Helper\EntityArrayHelperContract;
use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;
use TempestTools\Crud\Orm\Builder\ArrayToAssociationPropertyBuilder;
use TempestTools\Crud\Orm\Builder\ArrayToFieldPropertyBuilder;
use TempestTools\Crud\Orm\Builder\PrePersistEntityBuilder;

/**
 * An array helper with functionality added to process data related to entities.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class EntityArrayHelper extends ArrayHelper implements EntityArrayHelperContract
{
    use AccessorMethodNameTrait;

    /** @var  ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder */
    protected $arrayToFieldPropertyBuilder;

    /** @var  ArrayToAssociationPropertyBuilderContract  $arrayToAssociationPropertyBuilder*/
    protected $arrayToAssociationPropertyBuilder;

    /** @var  PrePersistEntityBuilderContract $persistEntityBuilder*/
    protected $prePersistEntityBuilder;

    /**
     * EntityArrayHelper constructor.
     *
     * @param ArrayObject|null $array
     * @param ArrayToFieldPropertyBuilderContract|null $arrayToFieldPropertyBuilder
     * @param ArrayToAssociationPropertyBuilderContract|null $arrayToAssociationPropertyBuilder
     * @param PrePersistEntityBuilderContract|null $prePersistEntityBuilder
     */
    public function __construct(
        ArrayObject $array = null,
        /** @noinspection PhpHierarchyChecksInspection */
        ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder =null,
        ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder =null,
        PrePersistEntityBuilderContract $prePersistEntityBuilder =null
    )
    {
        $arrayToFieldPropertyBuilder = $arrayToFieldPropertyBuilder ?? new ArrayToFieldPropertyBuilder();
        $arrayToAssociationPropertyBuilder = $arrayToAssociationPropertyBuilder ?? new ArrayToAssociationPropertyBuilder();
        $prePersistEntityBuilder = $prePersistEntityBuilder ?? new PrePersistEntityBuilder();
        $this->setArrayToFieldPropertyBuilder($arrayToFieldPropertyBuilder);
        $this->setArrayToAssociationPropertyBuilder($arrayToAssociationPropertyBuilder);
        $this->setPrePersistEntityBuilder($prePersistEntityBuilder);
        parent::__construct($array);
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Converts an entity to an array and handles all the rules and chaining involved there in.
     * @param EntityContract $entity
     * @param array $settings
     * @param mixed $slatedToTransform
     * @return array
     * @throws \RuntimeException
     */
    public function toArray(EntityContract $entity, array $settings, $slatedToTransform = null):array
    {

        $eventArgs = $entity->makeEventArgs($settings);
        $eventManager = $entity->getEventManager();
        /** @noinspection NullPointerExceptionInspection */
        $eventManager->dispatchEvent(EntityEventsConstants::PRE_TO_ARRAY, $eventArgs);
        $args = $eventArgs->getArgs()['params'];

        if ($settings['recompute'] === true) {
            $entity->setLastToArray();
        }

        if ($settings['useStored'] === true && $entity->getLastToArray() !== null) {
            return $entity->getLastToArray();
        }

        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        $returnArray =  $this->toArrayCore($entity, $args, $slatedToTransform);

        $args['returnArray'] = $returnArray;
        $eventArgs = $entity->makeEventArgs($args);
        /** @noinspection NullPointerExceptionInspection */
        $eventManager->dispatchEvent(EntityEventsConstants::POST_TO_ARRAY, $eventArgs);
        $returnArray = $eventArgs->getArgs()['params']['returnArray'];

        if ($settings['store'] === true) {
            $entity->setLastToArray($returnArray);
        }

        return $returnArray;
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * The core functionality of the toArray features
     * @param EntityContract $entity
     * @param array $settings
     * @param null $slatedToTransform
     * @return array
     * @throws \RuntimeException
     */
    public function toArrayCore(EntityContract $entity, array $settings, $slatedToTransform = null):array
    {
        /** @noinspection NullPointerExceptionInspection */
        $config = $this->getArray();
        $arrayHelper = $entity->getArrayHelper();

        $toArray = $config['toArray'] ?? null;
        $frontEndOptions = $settings['frontEndOptions'] ?? [];
        $completeness = $frontEndOptions['toArray']['completeness'] ?? 'full';
        $maxDepth  = $frontEndOptions['toArray']['maxDepth'] ?? null;
        $maxDepth = $maxDepth !== null?(int)$maxDepth:$maxDepth;
        $excludeKeys = $frontEndOptions['toArray']['excludeKeys'] ?? [];
        $allowOnlyRequestedParams = $frontEndOptions['toArray']['allowOnlyRequestedParams'] ?? true;
        $allowOnlyRequestedParams = (bool)$allowOnlyRequestedParams;
        $forceIncludeKeys = $frontEndOptions['toArray']['forceIncludeKeys'] ?? ['id'];
        $boundParams = $entity->getBindParams() ?? [];

        if ($slatedToTransform === null) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $slatedToTransform = $completeness === 'full' ? [] : new ArrayObject();
            $slatedToTransform['objects'] = [];
            $slatedToTransform['depth'] = 1;
        }

        $returnArray = [];

        $loopDetected = in_array($entity, $slatedToTransform['objects'], true);
        $slatedToTransform['objects'][] = $entity;
        $slatedToTransform['depth']++;

        if ($completeness === 'none' || ($maxDepth !== null && $slatedToTransform['depth'] > $maxDepth)) {
            return [];
        }

        if ($toArray !== null) {
            foreach ($toArray as $key => $value) {
                if (in_array($key, $excludeKeys, true) === false) {
                    $propertyValue = null;
                    if ($value !== null) {
                        $type = $value['type'] ?? 'get';
                        switch ($type) {
                            case 'get':
                                $methodName = $this->accessorMethodName('get', $key);
                                $propertyValue = $entity->$methodName();
                                break;
                            case 'literal':
                                $propertyValue = $arrayHelper->parse($value['value'], ['self'=>$entity, 'key'=>$key, 'value'=>$value, 'config'=>$config, 'toArrayConfig'=>$toArray, 'arrayHelper'=>$arrayHelper, 'configArrayHelper'=>$this, 'settings'=>$settings]);
                                break;
                        }
                    }
                    if (
                        (
                            $loopDetected === false
                            ||
                            (
                                $completeness !== 'minimal' && is_object($propertyValue) === false
                            )
                        )
                        &&
                        (
                            $allowOnlyRequestedParams === false
                            ||
                            array_key_exists($key, $boundParams) === true
                            ||
                            in_array($key, $forceIncludeKeys, true) === true
                        )

                    ) {
                        $returnArray[$key] = $entity->parseToArrayPropertyValue($propertyValue, $value, $settings, $slatedToTransform);
                    }
                }
            }
        }
        $slatedToTransform['depth']--;
        return $returnArray;
    }

    /**
     * Gets configurations for a specific field
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
     * Checks if the type of chaining that is requested is allowed for the requested association in the config for the entity
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain (EntityContract $entity, string $associationName, string $chainType, bool $nosey = true):bool
    {
        $extra = ['associationName'=>$associationName, 'chainType'=> $chainType, 'self'=>$entity];
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        $fieldSettings = $this->getFieldSettings($associationName);

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse(
            $entity->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType),
            $extra
        );

        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::chainTypeNotAllow($chainType, $associationName);
        }

        return $allowed;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Checks if the requested assignment type is allowed for the specified field in the current config
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
        $extra = ['associationName'=>$associationName, 'assignType'=> $assignType, 'self'=>$entity];
        $assignType = $assignType ?? 'null';
        if (!in_array($assignType, ['set', 'add', 'remove', 'setSingle', 'addSingle', 'removeSingle', 'null', 'setNull'], true)) {
            throw EntityArrayHelperException::assignTypeMustBe($assignType);
        }

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $this->getArray();
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $fieldSettings ?? $this->getFieldSettings($associationName);

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse(
            $entity->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType),
            $extra
        );

        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::assignTypeNotAllow($assignType, $associationName);
        }
        return $allowed;
    }

    /**
     * Checks if the current operation is allowed in the config for the entity
     * @param EntityContract $entity
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function allowed (EntityContract $entity, $nosey = true):bool {

        /** @noinspection NullPointerExceptionInspection */
        $array = $this->getArray();
        $allowed = $array['allowed'] ?? true;

        /** @noinspection NullPointerExceptionInspection */
        $allowed = $entity->getArrayHelper()->parse($allowed, ['self'=>$entity]);
        if ($nosey === true && $allowed === false) {
            throw EntityArrayHelperException::actionNotAllow($entity);
        }
        return $allowed;
    }


    /**
     * Gets settings for a specific field in the config for the entity
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws RuntimeException
     */
    public function getFieldSettings (string $fieldName, array $params = []):array {
        $fieldSettings = $this->parseArrayPath(['fields', $fieldName], $params, false, false)??[];
        return $fieldSettings;
    }

    /**
     * Processes the data to be set on a specific field
     * @param EntityContract $entity
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField (EntityContract $entity, array $params) {
        $fieldName = $params['fieldName'];
        $value = $params['value'];

        /** @noinspection NullPointerExceptionInspection */
        /** @var array[] $fieldSettings['settings'] */
        $fieldSettings = $this->getFieldSettings($fieldName);

        /** @noinspection NullPointerExceptionInspection */
        $this->canAssign($entity, $fieldName, 'set', $fieldSettings);
        if (isset($fieldSettings['settings'])) {
            $builder = $this->getArrayToFieldPropertyBuilder();
            $arrayHelper = $entity->getArrayHelper();
            foreach($fieldSettings['settings'] as $key => $fieldSetting) {
                $value = $builder->$key($arrayHelper, $fieldName, $value, $params, $fieldSetting);
            }
        }
        return $value;
    }

    /**
     * Processes the params for a passed for a specific association
     * @param EntityContract $entity
     * @param array $params
     * @return array
     * @throws \RuntimeException
     */
    protected function processAssociationParamsCore(EntityContract $entity, array $params):array {
        $associationName = $params['associationName'];
        $values = $params['values'];

        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getFieldSettings($associationName);

        // Check if assignment and chaining settings are allowed
        $chainType = $values['chainType'] ?? null;
        if ($chainType !== null) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canChain($entity, $associationName, $chainType);
        }
        /** @var array[] $fieldSettings['settings'] */
        if (isset($fieldSettings['settings'])) {
            $builder = $this->getArrayToAssociationPropertyBuilder();
            $arrayHelper = $entity->getArrayHelper();
            foreach($fieldSettings['settings'] as $key => $fieldSetting) {
                $values = $builder->$key($arrayHelper, $associationName, $values, $params, $fieldSetting);
            }
        }
        return $values;
    }

    /**
     * Runs on pre persist to validate and mutate the entity
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    protected function processPrePersist(EntityContract $entity) {
        $settings = $this->getArray()['settings'] ?? null;
        if ($settings !== null) {
            $builder = $this->getPrePersistEntityBuilder();
            foreach($settings as $key => $fieldSetting) {
                if ($fieldSetting !== null) {
                    $builder->$key($entity, $fieldSetting);
                }
            }
        }
    }



    /**
     * Sets a field on an entity
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @throws \RuntimeException
     */
    public function setField(EntityContract $entity, string $fieldName, $value):void
    {
        $params = ['fieldName' => $fieldName, 'value' => $value, 'configArrayHelper' => $this, 'self' => $entity];
        $eventArgs = $entity->makeEventArgs($params);

        // Give event listeners a chance to do something then pull out the args again
        /** @noinspection NullPointerExceptionInspection */
        $entity->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_SET_FIELD, $eventArgs);

        $processedParams = $eventArgs->getArgs()['params'];
        $value = $this->processSetField($entity, $processedParams);

        // All is ok so set it
        $setName = $this->accessorMethodName('set', $fieldName);
        $entity->$setName($value);
    }

    /**
     * Processes the params for an association
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(EntityContract $entity, string $associationName, array $values): array
    {
        $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $this, 'self' => $entity];
        $eventArgs = $entity->makeEventArgs($params);
        // Give event listeners a chance to do something and pull the args out again after wards
        /** @noinspection NullPointerExceptionInspection */
        $entity->getEventManager()->dispatchEvent(EntityEventsConstants::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

        $processedParams = $eventArgs->getArgs()['params'];
        /** @noinspection NullPointerExceptionInspection */
        $values = $this->processAssociationParamsCore($entity, $processedParams);

        return $values;

    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Binds data for a specific association on the entity
     * @param EntityContract $entity
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entityToBind
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(EntityContract $entity, string $assignType=null, string $associationName, EntityContract $entityToBind=null, $force = false):void
    {
        if ($force === false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->canAssign($entity, $associationName, $assignType);
        }
        if ($assignType === 'setNull') {
            $methodName = $this->accessorMethodName('set', $associationName);
            $entity->$methodName(null);
        } else if ($assignType !== null && $assignType !== 'null') {
            $methodName = $this->accessorMethodName($assignType, $associationName);
            $entity->$methodName($entityToBind);
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
     * Gets the values for specified fields on teh current entity
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

    /**
     * @return ArrayToFieldPropertyBuilderContract
     */
    public function getArrayToFieldPropertyBuilder(): ArrayToFieldPropertyBuilderContract
    {
        return $this->arrayToFieldPropertyBuilder;
    }

    /**
     * @param ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder
     */
    public function setArrayToFieldPropertyBuilder(ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder)
    {
        $this->arrayToFieldPropertyBuilder = $arrayToFieldPropertyBuilder;
    }

    /**
     * @return ArrayToAssociationPropertyBuilderContract
     */
    public function getArrayToAssociationPropertyBuilder(): ArrayToAssociationPropertyBuilderContract
    {
        return $this->arrayToAssociationPropertyBuilder;
    }

    /**
     * @param ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder
     */
    public function setArrayToAssociationPropertyBuilder(ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder)
    {
        $this->arrayToAssociationPropertyBuilder = $arrayToAssociationPropertyBuilder;
    }

    /**
     * @return PrePersistEntityBuilderContract
     */
    public function getPrePersistEntityBuilder(): PrePersistEntityBuilderContract
    {
        return $this->prePersistEntityBuilder;
    }

    /**
     * @param PrePersistEntityBuilderContract $prePersistEntityBuilder
     */
    public function setPrePersistEntityBuilder(PrePersistEntityBuilderContract $prePersistEntityBuilder)
    {
        $this->prePersistEntityBuilder = $prePersistEntityBuilder;
    }


}
?>