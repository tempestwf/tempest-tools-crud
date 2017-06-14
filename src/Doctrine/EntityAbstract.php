<?php
namespace TempestTools\Crud\Doctrine;

use App\Entities\Entity;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use TempestTools\Common\Contracts\ArrayHelpable;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;
use TempestTools\Common\Contracts\Evm;
use TempestTools\Common\Contracts\TTConfig;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\EntityEvents;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;


abstract class EntityAbstract extends Entity implements EventSubscriber, TTConfig, Evm, ArrayHelpable {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    const ERRORS = [
        'fieldNotAllow'=>[
            'message'=>'Error: Access to field not allowed.'
        ]
    ];

    /**
     * Makes sure the entity is ready to go
     *
     * @param ArrayHelperContract $arrayHelper
     * @throws \RuntimeException
     */
    public function __construct(ArrayHelperContract $arrayHelper) {
        $this->setArrayHelper($arrayHelper);
        $this->parseTTConfig();
        $this->setEvm(new EventManager());
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($this);
    }

    /**
     * @param string $action
     * @param string $fieldName
     * @param $value
     * @throws \RuntimeException
     */
    public function setField(string $action, string $fieldName, $value){
        $config = $this->getTTConfigParsed();
        $baseArrayHelper = $this->getArrayHelper();
        $arrayHelper = new ArrayHelper($config);
        $actionSettings = $arrayHelper->parseArrayPath([$action]);
        $fieldSettings = $arrayHelper->parseArrayPath([$action, 'fields', $fieldName]);
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
            throw new \RuntimeException($this->getErrorFromConstant('fieldNotAllow'));
        }

        // setTo or mutate value
        $value = isset($fieldSettings['setTo'])?$baseArrayHelper->parse($fieldSettings['setTo']):$value;
        $value = isset($fieldSettings['mutate']) === false?$baseArrayHelper->parse($fieldSettings['mutate'], ['value'=>$value]):$value;

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
        return new GenericEventArgs(new \ArrayObject(['params'=>$params,'arrayHelper'=>$this->getArrayHelper()]));
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