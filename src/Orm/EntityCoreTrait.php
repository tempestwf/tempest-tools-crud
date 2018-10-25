<?php
namespace TempestTools\Scribe\Orm;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Scribe\Constants\EntityEventsConstants;
use TempestTools\Scribe\Contracts\Orm\EntityContract;
use TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Scribe\Orm\Helper\EntityArrayHelper;
use TempestTools\Scribe\Contracts\Orm\Helper\EntityArrayHelperContract;
use Doctrine\ORM\Mapping as ORM;
use TempestTools\Scribe\Orm\Utility\EventManagerWrapperTrait;

/**
 * A trait that adds entity functionality to a class that facilitates use of the functionality of the package
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
trait EntityCoreTrait
{
    use TTConfigTrait, EvmTrait, EventManagerWrapperTrait;

    /**
     * @var array $bindParams
     */
    protected $bindParams;
    /**
     * @var string|null $lastMode
     */
    protected $lastMode;

    /** @var  array $lastToArray */
    protected $lastToArray;

    /** @var  bool $prePopulated */
    protected $prePopulated = false;

    /** @var array  */
    protected /** @noinspection PropertyCanBeStaticInspection */ $availableModes = ['create', 'read', 'update', 'delete'];

    /** @noinspection MoreThanThreeArgumentsInspection */

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Converts the entity into an array representation of it's fields values
     * @param array $settings
     * @param mixed $slatedToTransform
     * @return array
     */
    public function toArray(array $settings, $slatedToTransform = null):array
    {
        $settings['defaultMode'] = $settings['defaultMode'] ?? 'read';
        $settings['defaultArrayHelper'] = $settings['defaultArrayHelper'] ?? null;
        $settings['defaultPath'] = $settings['defaultPath'] ?? null;
        $settings['defaultFallBack'] = $settings['defaultFallBack'] ?? null;
        $settings['force'] = $settings['force'] ?? false;
        $settings['store'] = $settings['store'] ?? true;
        $settings['recompute'] = $settings['recompute'] ?? false;
        $settings['useStored'] = $settings['useStored'] ?? true;
        $settings['frontEndOptions'] = $settings['frontEndOptions'] ?? [];
        $mode = $this->getLastMode() ?? $settings['defaultMode'];
        $this->init($mode, $settings['defaultArrayHelper'], $settings['defaultPath'], $settings['defaultFallBack'], $settings['force']);

        /** @noinspection PhpParamsInspection */
        return $this->getConfigArrayHelper()->toArray($this, $settings, $slatedToTransform);
    }

    /**
     * Utilized by toArray functionality to convert a property of the entity to an array representation.
     * @param $propertyValue
     * @param array $settings
     * @param bool $force
     * @return mixed
     * @internal param array $value
     */
    abstract public function parseToArrayPropertyValue($propertyValue, array $settings = [], bool $force = false);

    /**
     * Initializes the entity array helper
     * @param bool $force
     * @param string $mode
     * @throws \RuntimeException
     */
    protected function entityArrayHelperInit(bool $force = false, string $mode):void
    {
        if ($force === true || $this->getConfigArrayHelper() === null || $mode !== $this->getLastMode()) {
            $entityArrayHelper = new EntityArrayHelper();
            $this->parseTTConfig($entityArrayHelper);
        }
    }

    /**
     * Sets a field on the entity
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value):void
    {
        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        $this->getConfigArrayHelper()->setField($this, $fieldName, $value);
    }


    /**
     * Processes the params for an association of the entity
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(string $associationName, array $values): array
    {
        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        return $this->getConfigArrayHelper()->processAssociationParams($this, $associationName, $values);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Binds data to an association of the entity
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entityToBind
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(string $assignType=null, string $associationName, EntityContract $entityToBind=null, $force = false):void
    {
        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        $this->getConfigArrayHelper()->bindAssociation($this, $assignType, $associationName, $entityToBind, $force);
    }

    /**
     * Makes event args to use with the events system
     *
     * @param array $params
     * @return \TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract
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
     * @ORM\PreUpdate
     * @throws \RuntimeException
     */
    public function ttPrePersist():void
    {
        $arrayHelper = $this->getConfigArrayHelper();
        if ($arrayHelper !== NULL) {
            /** @noinspection PhpParamsInspection */
            $arrayHelper->ttPrePersist($this);
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
     * Uses the validation factory to validate the data passed to the method.
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \RuntimeException
     */
    abstract public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []):void;


    /**
     * Gets the values for specified fields on the current entity
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(array $fields = []): array
    {
        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        return $this->getConfigArrayHelper()->getValuesOfFields($this, $fields);
    }

    /**
     * Gets the params that were bound to the entity
     * @return array|null
     */
    public function getBindParams(): ?array
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
     * Verifies the current operation is allowed
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function allowed($nosey = true): bool
    {
        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpParamsInspection */
        return $this->getConfigArrayHelper()->allowed($this, $nosey);
    }

    /**
     * @return NULL|\TempestTools\Scribe\Contracts\Orm\Helper\EntityArrayHelperContract
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

    /**
     * @return bool
     */
    public function isPrePopulated(): bool
    {
        return $this->prePopulated;
    }

    /**
     * @param bool $prePopulated
     */
    public function setPrePopulated(bool $prePopulated): void
    {
        $this->prePopulated = $prePopulated;
    }

    /**
     * @return mixed
     */
    public function getLastToArray():?array
    {
        return $this->lastToArray;
    }

    /**
     * @param array $lastToArray
     */
    public function setLastToArray(array $lastToArray=null):void
    {
        $this->lastToArray = $lastToArray;
    }

    /**
     * @return array
     */
    abstract public function getTTConfig(): array;

    /**
     * @return array
     */
    public function getAvailableModes(): array
    {
        return $this->availableModes;
    }

}
?>