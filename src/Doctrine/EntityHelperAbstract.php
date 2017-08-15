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
use TempestTools\Crud\Constants\EntityEventsConstants;
use TempestTools\Crud\Contracts\EntityHelperContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Orm\Helper\EntityHelper;
use TempestTools\Crud\Contracts\EntityArrayHelperContract;
use Doctrine\ORM\Mapping as ORM;


abstract class EntityHelperAbstract implements EventSubscriber, HasId, EntityHelperContract
{

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait, AccessorMethodNameTrait;

    const ERRORS = [
        'noArrayHelper' => [
            'message' => 'Error: No array helper on entity.',
        ],
        'enforcementFails' => [
            'message' => 'Error: A field is not set to it\'s enforced value. Value is %s, value should be %s',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass.',
        ],
        'validateFactoryNotIncluded' => [
            'message' => 'Error: Validation factory is not included on this class.',
        ],
        'validateNotIncluded' => [
            'message' => 'Error: Validation is not included on this class. It was passed these param: %s',
        ],
        'prePersistValidatorFails' => [
            'message' => 'Error: Validation failed on pre-persist.',
        ],
    ];
    /**
     * @var array $bindParams
     */
    protected $bindParams;

    /**
     * @var string|null $lastMode
     */
    protected $lastMode;

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
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false)
    {
        $startPath = $this->getTTPath();
        $startFallback = $this->getTTFallBack();
        if ($arrayHelper !== null && ($force === true || $this->getArrayHelper() === null)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== null && ($force === true || $this->getTTPath() === null || $mode !== $this->getLastMode() || $path !== $this->getTTPath())) {
            $this->setTTPath($path);
            $path = $this->getTTPath();
            $path[] = $mode;
            $this->setTTPath($path);
        }


        if ($fallBack !== null && ($force === true || $this->getTTFallBack() === null || $mode !== $this->getLastMode() || $fallBack !== $this->getTTFallBack())) {
            $this->setTTFallBack($fallBack);
            $path = $this->getTTFallBack();
            $path[] = $mode;
            $this->setTTFallBack($path);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper')['message']);
        }

        if ($force === true || $this->getConfigArrayHelper() === null || $mode !== $this->getLastMode() || $startPath !== $this->getTTPath() || $startFallback !== $this->getTTFallBack()) {
            $entityArrayHelper = new EntityHelper();
            $entityArrayHelper->setArrayHelper($this->getArrayHelper());
            $this->parseTTConfig($entityArrayHelper);
        }

        $this->setLastMode($mode);
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
            $configArrayHelper = $this->getConfigArrayHelper();
            $params = ['fieldName' => $fieldName, 'value' => $value, 'configArrayHelper' => $configArrayHelper, 'self' => $this];
            $eventArgs = $this->makeEventArgs($params);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEventsConstants::PRE_SET_FIELD, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            $value = $configArrayHelper->processSetField($processedParams);

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
            $configArrayHelper = $this->getConfigArrayHelper();

            $params = ['associationName' => $associationName, 'values' => $values, 'configArrayHelper' => $configArrayHelper, 'self' => $this];
            $eventArgs = $this->makeEventArgs($params);
            // Give event listeners a chance to do something and pull the args out again after wards
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEventsConstants::PRE_PROCESS_ASSOCIATION_PARAMS, $eventArgs);

            $processedParams = $eventArgs->getArgs()['params'];
            /** @noinspection NullPointerExceptionInspection */
            $values = $this->getConfigArrayHelper()->processAssociationParams($processedParams);

        }
        return $values;

    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityHelperContract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(string $assignType=null, string $associationName, EntityHelperContract $entity = null, $force = false)
    {
        if ($force === false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getConfigArrayHelper()->canAssign($associationName, $assignType);
        }

        if ($assignType !== null) {
            $methodName = $this->accessorMethodName($assignType, $associationName);
            $this->$methodName($entity);
        }

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
        $all = EntityEventsConstants::getAll();
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
        $arrayHelper = $this->getConfigArrayHelper();
        if ($arrayHelper !== NULL) {

            $eventArgs = $this->makeEventArgs([]);

            // Give event listeners a chance to do something then pull out the args again
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEventsConstants::PRE_PERSIST, $eventArgs);

            $arrayHelper->processPrePersist($this);

            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->dispatchEvent(EntityEventsConstants::POST_PERSIST, $eventArgs);
        }
    }



    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory()
    {
        throw new RuntimeException($this->getErrorFromConstant('validateFactoryNotIncluded')['message']);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     *
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \RuntimeException
     */
    public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []):void
    {
        $string = json_encode(['values'=>$values, 'rules'=>$rules, 'messages'=>$messages, 'customAttributes'=>$customAttributes]);
        throw new RuntimeException(sprintf($this->getErrorFromConstant('validateFactoryNotIncluded')['message'], $string));
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
            $value = $this->$methodName();
            $result[$field] = $value;
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

    /**
     * @return null|string
     */
    public function getLastMode():?string
    {
        return $this->lastMode;
    }

    /**
     * @param null|string $lastMode
     */
    public function setLastMode(string $lastMode = null)
    {
        $this->lastMode = $lastMode;
    }

}
?>