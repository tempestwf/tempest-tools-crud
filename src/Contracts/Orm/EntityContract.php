<?php

namespace TempestTools\Crud\Contracts\Orm;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use Doctrine\ORM\Mapping as ORM;
use TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Contracts\Orm\Helper\EntityArrayHelperContract;

interface EntityContract
{

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false):void;


    /**
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value):void;

    /**
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(string $associationName, array $values): array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(string $assignType, string $associationName, EntityContract $entity = null, $force = false):void;


    /**
     * Subscribes to the available events that are present on the class
     *
     * @return array
     */
    public function getSubscribedEvents(): array;

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @ORM\PrePersist
     * @throws \RuntimeException
     */
    public function ttPrePersist():void;


    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory();

    /**
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(array $fields = []): array;
    /**
     * @return array
     */
    public function getBindParams(): array;

    /**
     * @param array $bindParams
     */
    public function setBindParams(array $bindParams):void;
    /**
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function allowed($nosey = true): bool;

    /**
     * @return NULL|EntityArrayHelperContract
     */
    public function getConfigArrayHelper():?EntityArrayHelperContract;

    /**
     * @param EntityArrayHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(EntityArrayHelperContract $configArrayHelper):void;


    /**
     * @param null|ArrayHelperContract $arrayHelper
     */
    public function setArrayHelper(ArrayHelperContract $arrayHelper):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     *
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \RuntimeException
     */
    public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []):void;

    /**
     * @param array $params
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params): GenericEventArgsContract;

    /**
     * @return EventManagerWrapperContract
     */
    public function getEventManager(): EventManagerWrapperContract;
    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper):void;

    /**
     * Passes it's self to the extractor
     *
     * @return \ArrayObject
     * @throws \RuntimeException
     */
    public function extractSelf (): \ArrayObject;

    /**
     * @return null|ArrayHelperContract
     */
    public function getArrayHelper():?ArrayHelperContract;

    /**
     * Gets existing array helper, or makes new one and then returns it
     *
     * @return null|ArrayHelperContract
     */
    public function arrayHelper():ArrayHelperContract;


    /**
     * Tags a config and a path, gets the element in the path in the config, and then uses an array helper to parse
     * it's inheritance. Sets the result on parsedConfig property
     *
     * @param ArrayHelperContract|null $substituteArrayHelper
     * @return array
     * @throws \RuntimeException
     */
    public function parseTTConfig(ArrayHelperContract $substituteArrayHelper = NULL):array;

    /**
     * @return array
     */
    public function getTTConfig(): array;

    /**
     * @param array $ttPath
     */
    public function setTTPath(array $ttPath): void;

    /**
     * @param array $ttFallBack
     */
    public function setTTFallBack(array $ttFallBack): void;
    /**
     * @return NULL|array
     */
    public function getTTPath(): ?array;
    /**
     * @return NULL|array
     */
    public function getTTFallBack(): ?array;

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Common logic for checking if the permissive settings allow something to be don
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $canDo
     * @param string $target
     * @return bool
     */
    public function permissivePermissionCheck ($high, array $low, string $canDo, string $target):bool;
    /**
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $setting
     * @return bool|mixed|null
     */
    public function highLowSettingCheck($high, array $low = NULL, string $setting);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Common logic for checking if the permissive settings allow something to be don
     * @param array|\ArrayObject $high
     * @param array $low
     * @return bool
     */
    public function permissiveAllowedCheck ($high, array $low):bool;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @param string|null $mode
     * @throws \RuntimeException
     * @return bool
     */
    public function coreInit (ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true, string $mode = null):bool;

    /**
     * @return \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract
     * @throws \RuntimeException
     */
    public function createEventManagerWrapper ():EventManagerWrapperContract;

}