<?php
namespace TempestTools\Crud\Orm;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\EntityEventsConstants;
use TempestTools\Crud\Contracts\EntityContract;
use TempestTools\Crud\Contracts\GenericEventArgsContract;
use TempestTools\Crud\Orm\Helper\EntityArrayHelper;
use TempestTools\Crud\Contracts\EntityArrayHelperContract;
use Doctrine\ORM\Mapping as ORM;


trait EntityCoreTrait
{
    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait, EventManagerWrapperTrait;

    /**
     * @var array $bindParams
     */
    protected $bindParams;

    /**
     * @var string|null $lastMode
     */
    protected $lastMode;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false):void
    {
        $force = $this->coreInit($arrayHelper, $path, $fallBack, $force);
        $this->entityArrayHelperInit($force, $mode);
        $this->setLastMode($mode);
    }

    /**
     * @param bool $force
     * @param string $mode
     * @throws \RuntimeException
     */
    protected function entityArrayHelperInit(bool $force = false, string $mode):void
    {
        if ($force === true || $this->getConfigArrayHelper() === null || $mode !== $this->getLastMode()) {
            $entityArrayHelper = new EntityArrayHelper();
            /** @noinspection PhpParamsInspection */
            $entityArrayHelper->setEntity($this);
            $this->parseTTConfig($entityArrayHelper);
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
        $this->getConfigArrayHelper()->setField($fieldName, $value);
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
        return $this->getConfigArrayHelper()->processAssociationParams($associationName, $values);
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
        /** @noinspection NullPointerExceptionInspection */
        $this->getConfigArrayHelper()->bindAssociation($assignType, $associationName, $entity, $force);
    }

    /**
     * Makes event args to use
     *
     * @param array $params
     * @return GenericEventArgsContract
     */
    abstract public function makeEventArgs(array $params): GenericEventArgsContract;

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
    public function ttPrePersist():void
    {
        $arrayHelper = $this->getConfigArrayHelper();
        if ($arrayHelper !== NULL) {
            $arrayHelper->ttPrePersist();
        }
    }

    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    abstract public function getValidationFactory();

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     *
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \RuntimeException
     */
    abstract public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []):void;


    /**
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(array $fields = []): array
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getConfigArrayHelper()->getValuesOfFields($fields);
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
    public function setBindParams(array $bindParams):void
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
    public function setConfigArrayHelper(EntityArrayHelperContract $configArrayHelper):void
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
    public function setLastMode(string $lastMode = null):void
    {
        $this->lastMode = $lastMode;
    }

}
?>