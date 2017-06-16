<?php
namespace TempestTools\Crud\Doctrine;

use App\Entities\Entity;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\EntityEvents;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use Illuminate\Contracts\Validation\Factory;



abstract class EntityAbstract extends Entity implements EventSubscriber {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait, AccessorMethodNameTrait;

    const ERRORS = [
        'fieldNotAllow'=>[
            'message'=>'Error: Access to field not allowed.',
        ],
        'noArrayHelper'=>[
            'message'=>'Error: No array helper on entity.',
        ],
        'chainTypeNotAllow'=>[
            'message'=>'Error: Requested chain type not permitted.',
        ],
        'assignTypeNotAllow'=>[
            'message'=>'Error: Requested assign type not permitted.',
        ],
        'assignTypeMustBe'=>[
            'message'=>'Error: Assign type must be set, add or remove.',
        ],
        'enforcementFails'=>[
            'message'=>'Error: A field is not set to it\'s enforced value.',
        ],
        'closureFails'=>[
            'message'=>'Error: A validation closure did not pass.',
        ],
        'validateFactoryNotIncluded'=>[
            'message'=>'Error: Validation factory is not included on this class.',
        ],
        'prePersistValidatorFails'=>[
            'message'=>'Error: Validation failed on pre-persist.',
        ]
    ];

    /**
     * Makes sure the entity is ready to go
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        $this->setEvm(new EventManager());
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($this);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force = true)
    {
        if ($arrayHelper !== NULL && ($force === true || $this->getArrayHelper() === NULL)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== NULL && ($force === true || $this->getTTPath() === NULL)) {
            $this->setTTPath($path);
            $path = $this->getTTPath();
            $path[] = $mode;
            $this->setTTPath($path);
        }


        if ($fallBack !== NULL && ($force === true || $this->getTTFallBack() === NULL)) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        if ($force !== true || $this->getConfigArrayHelper() === NULL) {
            $this->parseTTConfig();
        }
    }

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     */
    public function getConfigForField(string $fieldName, string $keyName)
    {
        $arrayHelper = $this->getConfigArrayHelper();
        return $arrayHelper->parseArrayPath(['fields', $fieldName, $keyName]);
    }

    /**
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value)
    {
        $fastMode = $this->checkFastMode($fieldName);
        if ($fastMode !== true) {
            $baseArrayHelper = $this->getArrayHelper();
            $configArrayHelper = $this->getConfigArrayHelper();
            $params = ['fieldName'=>$fieldName, 'value'=>$value, 'configArrayHelper'=>$configArrayHelper];
            $eventArgs = $this->makeEventArgs($params);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEvents::PRE_SET_FIELD, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $fieldName = $processedParams['params']['fieldName'];
            $value = $processedParams['params']['value'];

            // Get the settings for the field so we can do quick comparisons
            $fieldSettings = $configArrayHelper->parseArrayPath(['fields', $fieldName]);
            $fieldSettings = $fieldSettings??[];

            // Check permission to set
            $allowed = $this->canAssign($fieldName, 'set');

            // Additional validation
            $allowed = isset($fieldSettings['enforce']) && $baseArrayHelper->parse($value) !== $baseArrayHelper->parse($fieldSettings['enforce'])?false:$allowed;
            // Any validation failure error out
            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
            }

            $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], ['fieldName'=>$fieldName, 'value'=>$value, 'self'=>$this]) === false?false:$allowed;

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('closureFails'));
            }


            // setTo or mutate value
            $value = isset($fieldSettings['setTo'])?$baseArrayHelper->parse($fieldSettings['setTo']):$value;
            $value = isset($fieldSettings['mutate'])?$baseArrayHelper->parse($fieldSettings['mutate'], ['fieldName'=>$fieldName, 'value'=>$value, 'self'=>$this]):$value;
        }
        // All is ok so set it
        $setName = $this->accessorMethodName('set', $fieldName);
        $this->$setName($value);
    }


    /**
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(string $associationName, array $values):array
    {
        $fastMode = $this->checkFastMode($associationName);
        if ($fastMode !== true) {
            $baseArrayHelper = $this->getArrayHelper();
            $configArrayHelper = $this->getConfigArrayHelper();

            $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $configArrayHelper];
            $eventArgs = $this->makeEventArgs($params);
            // Give event listeners a chance to do something and pull the args out again after wards
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEvents::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $associationName = $processedParams['params']['associationName'];
            $values = $processedParams['params']['values'];

            // Get the settings for the field so we can do quick comparisons
            $fieldSettings = $configArrayHelper->parseArrayPath(['fields', $associationName]);
            $fieldSettings = $fieldSettings??[];

            // Check if assignment and chaining settings are allowed
            $assignType = $values['assignType'] ?? 'set';
            $chainType = $values['chainType'] ?? null;
            if ($chainType !== null) {
                $this->canChain($associationName, $chainType);
            }
            $this->canAssign($associationName, $assignType);

            // Check if fields that are needed to be enforced as enforced
            $enforce = isset($fieldSettings['enforce']) ? $baseArrayHelper->parse($fieldSettings['enforce']) : [];

            $allowed = true;
            foreach ($enforce as $key => $value) {
                /** @noinspection NullPointerExceptionInspection */
                if ($values[$key] !== $this->getArrayHelper()->parse($value)) {
                    $allowed = false;
                    break;
                }
            }

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
            }

            // Run it through closure validation if there is a closure
            $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], ['associationName' => $associationName, 'values' => $values, 'self' => $this]) === false ? false : $allowed;

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('closureFails'));
            }



            // Figure out if there are values that need to be set to, and set it to those values if any found
            $setTo = isset($fieldSettings['setTo']) ? $baseArrayHelper->parse($fieldSettings['setTo']) : [];

            if ($setTo !== null) {
                $values = array_replace_recursive($values, $setTo);
            }

            // Run mutation closure if one is present
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $values = isset($fieldSettings['mutate']) ? $baseArrayHelper->parse($fieldSettings['mutate'], ['associationName' => $associationName, 'values' => $values, 'self' => $this]) : $values;
        }
        return $values;

    }

    /**
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain (string $associationName, string $chainType, bool $nosey = true):bool {
        $arrayHelper = $this->getConfigArrayHelper();
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $arrayHelper->getArray();
        $fieldSettings = $arrayHelper->parseArrayPath(['fields', $associationName]);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'chain', $chainType);

        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('chainTypeNotAllow')['message']);
        }

        return $allowed;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityAbstract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(string $assignType, string $associationName, EntityAbstract $entity, $force = false){
        if ($force === false) {
            $this->canAssign($assignType, $associationName);
        }
        if (!in_array($assignType, ['set', 'add', 'remove'], true)){
            throw new \RuntimeException($this->getErrorFromConstant('assignTypeMustBe')['message']);
        }
        $methodName = $this->accessorMethodName($assignType, $associationName);
        $this->$methodName($entity);
    }

    /**
     * @param string $associationName
     * @param string $assignType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canAssign (string $associationName, string $assignType, bool $nosey = true):bool {
        $arrayHelper = $this->getConfigArrayHelper();
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $arrayHelper->getArray();
        $fieldSettings = $arrayHelper->parseArrayPath(['fields', $associationName]);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);

        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('assignTypeNotAllow')['message']);
        }
        return $allowed;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function checkFastMode(string $fieldName):bool {
        $arrayHelper = $this->getConfigArrayHelper();

        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $arrayHelper->getArray();
        $fieldSettings = $arrayHelper->parseArrayPath(['fields', $fieldName]);
        return $this->highLowSettingCheck($actionSettings, $fieldSettings, 'fastMode');
    }
    /**
     * Makes event args to use
     * @param array $params
     * @return GenericEventArgs
     */
    protected function makeEventArgs(array $params): Events\GenericEventArgs
    {
        return new GenericEventArgs(new \ArrayObject(['params'=>$params,'arrayHelper'=>$this->getArrayHelper(), 'self'=>$this]));
    }

    /**
     * Subscribes to the available events that are present on the class
     * @return array
     */
    public function getSubscribedEvents():array
    {
        $all = EntityEvents::getAll();
        $subscribe = [];
        foreach ($all as $event) {
            if (method_exists ($this, $event)) {
                $subscribe[] = $event;
            }
        }
        return $subscribe;
    }

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @ORM\PrePersist
     * @throws \RuntimeException
     */
    public function ttPrePersist()
    {
        $eventArgs = $this->makeEventArgs([]);

        // Give event listeners a chance to do something then pull out the args again
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->dispatchEvent(EntityEvents::PRE_PERSIST, $eventArgs);

        $arrayHelper = $this->getConfigArrayHelper();
        $array = $arrayHelper->getArray();
        if (isset($array['setTo'])) {
            $this->ttPrePersistSetTo($array['setTo']);
        }

        if (isset($array['enforce'])) {
            $this->ttPrePersistEnforce($array['enforce']);
        }

        if (isset($array['closure'])) {
            $this->ttPrePersistClosure($array['closure']);
        }

        if (isset($array['mutate'])) {
            $this->ttPrePersistMutate($array['mutate']);
        }

        if (isset($array['validate'])) {
            $this->ttPrePersistValidate($array['validate']);
        }

        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->dispatchEvent(EntityEvents::POST_PERSIST, $eventArgs);
    }

    /**
     * @param array $validate
     * @throws \RuntimeException
     */
    protected function ttPrePersistValidate(array $validate) {
        /** @var Factory $factory */
        $factory = $this->getValidationFactory();
        $fields = $validate['fields'] ?? [];
        $rules = $validate['rules'] ?? [];
        $messages = $validate['messages'] ?? [];
        $customAttributes = $validate['customAttributes'] ?? [];
        $values = $this->getValuesOfFields($fields);
        $validator = $factory->make($values, $rules, $messages, $customAttributes);
        if($validator->fails())
        {
            throw new RuntimeException($this->getErrorFromConstant('prePersistValidatorFails'));
        }
    }

    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory() {
        throw new RuntimeException($this->getErrorFromConstant('validateFactoryNotIncluded'));
    }

    /**
     * @param callable $closure
     * @throws \RuntimeException
     */
    protected function ttPrePersistMutate (Callable $closure) {
        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->parseClosure($closure, ['self'=>$this]);
    }

    /**
     * @param callable $closure
     * @throws \RuntimeException
     */
    protected function ttPrePersistClosure (Callable $closure) {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parseClosure($closure, ['self'=>$this]);
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('closureFails'));
        }
    }

    /**
     * @param array $values
     */
    protected function ttPrePersistSetTo (array $values) {
        foreach ($values as $key => $value) {
            $methodName = $this->accessorMethodName('set', $key);
            $this->$methodName($value);
        }
    }

    /**
     * @param array $values
     * @throws \RuntimeException
     */
    protected function ttPrePersistEnforce (array $values) {
        foreach ($values as $key => $value) {
            $methodName = $this->accessorMethodName('get', $key);
            $result = $this->$methodName();
            if (!is_scalar ($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    $methodName = $this->accessorMethodName('get', $key2);
                    $result2 = $result->$methodName();
                    if ($result2 !== $value2) {
                        throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
                    }
                }
            } else if ($result !== $value) {
                throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
            }
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields (array $fields = []):array {
        $result = [];
        foreach ($fields as $key => $value) {
            $methodName = $this->accessorMethodName('get', $key);
            $value = $this->$methodName($value);
            $result[$key] = $value;
        }
        return $result;
    }
}


?>