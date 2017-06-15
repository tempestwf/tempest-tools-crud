<?php
namespace TempestTools\Crud\Doctrine;

use App\Entities\Entity;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\EntityEvents;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;


abstract class EntityAbstract extends Entity implements EventSubscriber {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    const ERRORS = [
        'fieldNotAllow'=>[
            'message'=>'Error: Access to field not allowed.'
        ],
        'noArrayHelper'=>[
            'message'=>'Error: No array helper on entity.'
        ],
        'chainTypeNotAllow'=>[
            'message'=>'Error: Requested chain type not permitted.'
        ],
        'assignTypeNotAllow'=>[
            'message'=>'Error: Requested assign type not permitted.'
        ],
        'assignTypeMustBe'=>[
            'message'=>'Error: Assign type must be set, add or remove'
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
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL)
    {
        if ($arrayHelper !== NULL) {
            $this->setArrayHelper($arrayHelper);
        }

        $path = $path ?? $this->getTTPath();

        if ($fallBack !== NULL) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        $path[] = $mode;
        $this->setTTPath($path);
        $this->parseTTConfig();
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
        $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], ['fieldName'=>$fieldName, 'value'=>$value, 'self'=>$this]) === false?false:$allowed;

        // Any validation failure error out
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('fieldNotAllow'));
        }

        // setTo or mutate value
        $value = isset($fieldSettings['setTo'])?$baseArrayHelper->parse($fieldSettings['setTo']):$value;
        $value = isset($fieldSettings['mutate'])?$baseArrayHelper->parse($fieldSettings['mutate'], ['fieldName'=>$fieldName, 'value'=>$value, 'self'=>$this]):$value;

        // All is ok so set it
        $setName = 'set' . ucfirst($fieldName);
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
        $baseArrayHelper = $this->getArrayHelper();
        $configArrayHelper = $this->getConfigArrayHelper();

        $params = ['associationName'=>$associationName, 'values'=>$values, 'configArrayHelper'=>$configArrayHelper];
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
        $chainType = $values['chainType'] ?? NULL;
        if ($chainType!==NULL) {
            $this->canChain($associationName, $chainType);
        }
        $this->canAssign($associationName, $assignType);

        // Check if fields that are needed to be enforced as enforced
        $enforce = isset($fieldSettings['enforce'])?$baseArrayHelper->parse($fieldSettings['enforce']):[];
        $allowed = !array_diff($enforce, $values);

        // Run it through closure validation if there is a closure
        $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'],['associationName'=>$associationName, 'values'=>$values, 'self'=>$this]) === false?false:$allowed;

        // Any validation failure error out
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('fieldNotAllow'));
        }

        // Figure out if there are values that need to be set to, and set it to those values if any found
        $setTo = isset($fieldSettings['setTo'])?$baseArrayHelper->parse($fieldSettings['setTo']):[];

        if ($setTo !== NULL) {
            $values = array_replace_recursive($values, $setTo);
        }

        // Run mutation closure if one is present
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $values = isset($fieldSettings['mutate'])?$baseArrayHelper->parse($fieldSettings['mutate'], ['associationName'=>$associationName, 'values'=>$values, 'self'=>$this]):$values;
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
        $actionSettings = $arrayHelper->getArray()->getArrayCopy();
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
        $methodName = $assignType . ucfirst($associationName);
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
        $actionSettings = $arrayHelper->getArray()->getArrayCopy();
        $fieldSettings = $arrayHelper->parseArrayPath(['fields', $associationName]);

        $allowed = $this->permissivePermissionCheck($actionSettings, $fieldSettings, 'assign', $assignType);

        if ($nosey === true && $allowed === false) {
            throw new \RuntimeException($this->getErrorFromConstant('assignTypeNotAllow')['message']);
        }
        return $allowed;
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
}


?>