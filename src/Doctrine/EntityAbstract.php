<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use RuntimeException;
use TempestTools\AclMiddleware\Contracts\HasId;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\AccessorMethodNameTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\EntityEvents;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use Illuminate\Contracts\Validation\Factory;
use TempestTools\Crud\Doctrine\Helper\EntityArrayHelper;
use TempestTools\Crud\Contracts\EntityArrayHelper as EntityArrayHelperContract;


abstract class EntityAbstract implements EventSubscriber, HasId
{

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait, AccessorMethodNameTrait;

    const ERRORS = [
        'noArrayHelper' => [
            'message' => 'Error: No array helper on entity.',
        ],
        'assignTypeMustBe' => [
            'message' => 'Error: Assign type must be set, add or remove.',
        ],
        'enforcementFails' => [
            'message' => 'Error: A field is not set to it\'s enforced value.',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass.',
        ],
        'validateFactoryNotIncluded' => [
            'message' => 'Error: Validation factory is not included on this class.',
        ],
        'prePersistValidatorFails' => [
            'message' => 'Error: Validation failed on pre-persist.',
        ]
    ];
    /**
     * @var array $bindParams
     */
    protected $bindParams;

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
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = true)
    {
        if ($arrayHelper !== null && ($force === true || $this->getArrayHelper() === null)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== null && ($force === true || $this->getTTPath() === null)) {
            $this->setTTPath($path);
            $path = $this->getTTPath();
            $path[] = $mode;
            $this->setTTPath($path);
        }


        if ($fallBack !== null && ($force === true || $this->getTTFallBack() === null)) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        if ($force !== true || $this->getConfigArrayHelper() === null) {
            $entityArrayHelper = new EntityArrayHelper();
            $entityArrayHelper->setArrayHelper($this->getArrayHelper());
            $this->parseTTConfig($entityArrayHelper);
        }
    }


    /**
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value)
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->getConfigArrayHelper()->checkFastMode($fieldName);
        if ($fastMode !== true) {
            $baseArrayHelper = $this->getArrayHelper();
            $configArrayHelper = $this->getConfigArrayHelper();
            $params = ['fieldName' => $fieldName, 'value' => $value, 'configArrayHelper' => $configArrayHelper, 'self' => $this];
            $eventArgs = $this->makeEventArgs($params);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEvents::PRE_SET_FIELD, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $fieldName = $processedParams['params']['fieldName'];
            $value = $processedParams['params']['value'];

            // Get the settings for the field so we can do quick comparisons
            $fieldSettings = $configArrayHelper->parseArrayPath(['fields', $fieldName], $processedParams);
            $fieldSettings = $fieldSettings??[];

            // Check permission to set
            /** @noinspection NullPointerExceptionInspection */
            $allowed = $this->getConfigArrayHelper()->canAssign($fieldName, 'set');

            // Additional validation
            $allowed = isset($fieldSettings['enforce']) && $baseArrayHelper->parse($value, $processedParams) !== $baseArrayHelper->parse($fieldSettings['enforce'], $processedParams) ? false : $allowed;

            // Any validation failure error out
            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
            }

            $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], $processedParams) === false ? false : $allowed;

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('closureFails'));
            }


            // setTo or mutate value
            $value = isset($fieldSettings['setTo']) ? $baseArrayHelper->parse($fieldSettings['setTo'], $processedParams) : $value;
            $value = isset($fieldSettings['mutate']) ? $baseArrayHelper->parse($fieldSettings['mutate'], $processedParams) : $value;
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
    public function processAssociationParams(string $associationName, array $values): array
    {
        /** @noinspection NullPointerExceptionInspection */
        $fastMode = $this->getConfigArrayHelper()->checkFastMode($associationName);
        if ($fastMode !== true) {
            $baseArrayHelper = $this->getArrayHelper();
            $configArrayHelper = $this->getConfigArrayHelper();

            $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $configArrayHelper, 'self' => $this];
            $eventArgs = $this->makeEventArgs($params);
            // Give event listeners a chance to do something and pull the args out again after wards
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEvents::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $associationName = $processedParams['params']['associationName'];
            $values = $processedParams['params']['values'];

            // Get the settings for the field so we can do quick comparisons
            $fieldSettings = $configArrayHelper->parseArrayPath(['fields', $associationName], $processedParams);
            $fieldSettings = $fieldSettings??[];

            // Check if assignment and chaining settings are allowed
            $assignType = $values['assignType'] ?? 'set';
            $chainType = $values['chainType'] ?? null;
            if ($chainType !== null) {
                /** @noinspection NullPointerExceptionInspection */
                $this->getConfigArrayHelper()->canChain($associationName, $chainType);
            }
            /** @noinspection NullPointerExceptionInspection */
            $this->getConfigArrayHelper()->canAssign($associationName, $assignType);

            // Check if fields that are needed to be enforced as enforced
            $enforce = isset($fieldSettings['enforce']) ? $baseArrayHelper->parse($fieldSettings['enforce'], $processedParams) : [];

            /** @noinspection NullPointerExceptionInspection */
            $allowed = $this->getArrayHelper()->testEnforceValues($values, $enforce, $processedParams);

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('enforcementFails'));
            }

            // Run it through closure validation if there is a closure
            $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], $processedParams) === false ? false : $allowed;

            if ($allowed === false) {
                throw new RuntimeException($this->getErrorFromConstant('closureFails'));
            }

            // Figure out if there are values that need to be set to, and set it to those values if any found
            $setTo = isset($fieldSettings['setTo']) ? $baseArrayHelper->parse($fieldSettings['setTo'], $processedParams) : [];

            if ($setTo !== null) {
                $values = array_replace_recursive($values, $setTo);
            }

            // Run mutation closure if one is present
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $values = isset($fieldSettings['mutate']) ? $baseArrayHelper->parse($fieldSettings['mutate'], $processedParams) : $values;
        }
        return $values;

    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityAbstract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(string $assignType, string $associationName, EntityAbstract $entity = null, $force = false)
    {
        if ($force === false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getConfigArrayHelper()->canAssign($assignType, $associationName);
        }
        if (!in_array($assignType, ['set', 'add', 'remove'], true)) {
            throw new \RuntimeException($this->getErrorFromConstant('assignTypeMustBe')['message']);
        }
        $methodName = $this->accessorMethodName($assignType, $associationName);
        $this->$methodName($entity);
    }

    /**
     * Makes event args to use
     *
     * @param array $params
     * @return GenericEventArgs
     */
    protected function makeEventArgs(array $params): Events\GenericEventArgs
    {
        return new GenericEventArgs(new \ArrayObject(['params' => $params, 'configArrayHelper' => $this->getConfigArrayHelper(), 'arrayHelper' => $this->getArrayHelper(), 'self' => $this]));
    }

    /**
     * Subscribes to the available events that are present on the class
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        $all = EntityEvents::getAll();
        $subscribe = [];
        foreach ($all as $event) {
            if (method_exists($this, $event)) {
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
    protected function ttPrePersistValidate(array $validate)
    {
        /** @var Factory $factory */
        $factory = $this->getValidationFactory();
        $fields = $validate['fields'] ?? [];
        $rules = $validate['rules'] ?? [];
        $messages = $validate['messages'] ?? [];
        $customAttributes = $validate['customAttributes'] ?? [];
        $values = $this->getValuesOfFields($fields);
        $validator = $factory->make($values, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            throw new RuntimeException($this->getErrorFromConstant('prePersistValidatorFails'));
        }
    }

    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory()
    {
        throw new RuntimeException($this->getErrorFromConstant('validateFactoryNotIncluded'));
    }

    /**
     * @param callable $closure
     * @throws \RuntimeException
     */
    protected function ttPrePersistMutate(Callable $closure)
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->parseClosure($closure, ['self' => $this]);
    }

    /**
     * @param callable $closure
     * @throws \RuntimeException
     */
    protected function ttPrePersistClosure(Callable $closure)
    {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parseClosure($closure, ['self' => $this]);
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('closureFails'));
        }
    }

    /**
     * @param array $values
     */
    protected function ttPrePersistSetTo(array $values)
    {
        $extra = ['self' => $this];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('set', $key);
            $this->$methodName($value);
        }
    }

    /**
     * @param array $values
     * @throws \RuntimeException
     */
    protected function ttPrePersistEnforce(array $values)
    {
        $extra = ['self' => $this];
        foreach ($values as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $value = $this->getArrayHelper()->parse($value, $extra);
            $methodName = $this->accessorMethodName('get', $key);
            $result = $this->$methodName();
            if (!is_scalar($result)) {
                /** @var array $value */
                foreach ($value as $key2 => $value2) {
                    /** @noinspection NullPointerExceptionInspection */
                    $value2 = $this->getArrayHelper()->parse($value2, $extra);
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
    public function getValuesOfFields(array $fields = []): array
    {
        $result = [];
        foreach ($fields as $key => $value) {
            $methodName = $this->accessorMethodName('get', $key);
            $value = $this->$methodName($value);
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getBindParams(): array
    {
        return $this->bindParams;
    }

    /**
     * @param array $bindParams
     */
    public function setBindParams(array $bindParams)
    {
        $this->bindParams = $bindParams;
    }

    /**
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function allowed($nosey = true): bool
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getConfigArrayHelper()->allowed($nosey);
    }

    /**
     * @return NULL|EntityArrayHelperContract
     */
    public function getConfigArrayHelper():?EntityArrayHelperContract
    {
        return $this->configArrayHelper;
    }

    /**
     * @param EntityArrayHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(EntityArrayHelperContract $configArrayHelper)
    {
        $this->configArrayHelper = $configArrayHelper;
    }

}
?>