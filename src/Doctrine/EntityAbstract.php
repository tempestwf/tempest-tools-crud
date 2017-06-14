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
        ]
    ];

    /**
     * Makes sure the entity is ready to go
     *
     * @throws RuntimeException
     */
    public function __construct() {
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
    public function init(string $mode, ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL) {
        if ($arrayHelper !== NULL) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== NULL) {
            $this->setTTPath($path);
        }

        if ($fallBack !== NULL) {
            $this->setTTFallBack($path);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        $path = $this->getTTPath();
        $path[] = $mode;
        $this->parseTTConfig();
    }

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     */
    public function getConfigForField(string $fieldName, string $keyName) {
        $arrayHelper = $this->getConfigArrayHelper();
        return $arrayHelper->parseArrayPath(['fields', $fieldName, $keyName]);
    }

    /**
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value){
        $baseArrayHelper = $this->getArrayHelper();
        $arrayHelper = $this->getConfigArrayHelper();
        $params = ['fieldName'=>$fieldName, 'value'=>$value, 'configArrayHelper'=>$arrayHelper];
        $eventArgs = $this->makeEventArgs($params);

        // Give event listeners a chance to do something
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->dispatchEvent(EntityEvents::PRE_SET_FIELD, $eventArgs);

        $processedParams = $eventArgs->getArgs()['params'];
        $fieldName = $processedParams['params']['fieldName'];
        $value = $processedParams['params']['value'];

        $actionSettings = $arrayHelper->getArray();
        $fieldSettings = $arrayHelper->parseArrayPath(['fields', $fieldName]);
        $actionPermissive = isset($actionSettings['permissive']) ?? $actionSettings['permissive'];
        $fieldPermissive = $fieldSettings !== NULL && isset($fieldSettings['permissive']) ?? $actionSettings['permissive'];

        // Check permission to set
        $allowed = true;
        $allowed = $actionPermissive === false && $fieldSettings === NULL?false:$allowed;
        $allowed = $fieldPermissive === false && (!isset($fieldSettings['assign']) || !isset($fieldSettings['assign']['set']) || $fieldSettings['assign']['set'] === false) ?false:$allowed;
        $allowed = $fieldPermissive === true && isset($fieldSettings['assign']) && isset($fieldSettings['assign']['set']) && $fieldSettings['assign']['set'] === false ?false:$allowed;

        // Additional validation
        $allowed = isset($fieldSettings['enforce']) && $baseArrayHelper->parse($value) !== $fieldSettings['enforce']?false:$allowed;
        $allowed = isset($fieldSettings['closure']) && $baseArrayHelper->parse($fieldSettings['closure'], ['value'=>$value]) === false?false:$allowed;

        // Any validation failure error out
        if ($allowed === false) {
            throw new RuntimeException($this->getErrorFromConstant('fieldNotAllow'));
        }

        // setTo or mutate value
        $value = isset($fieldSettings['setTo'])?$baseArrayHelper->parse($fieldSettings['setTo']):$value;
        $value = isset($fieldSettings['mutate']) === false?$baseArrayHelper->parse($fieldSettings['mutate'], ['fieldName'=>$fieldName, 'value'=>$value, 'self'=>$this]):$value;

        // All is ok so set it
        $setName = 'set' . ucfirst($fieldName);
        $this->$setName($value);
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