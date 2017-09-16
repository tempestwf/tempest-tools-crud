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

    /**
     * Passes it's self to the extractor
     *
     * @return \ArrayObject
     * @throws \RuntimeException
     */
    public function extractSelf(): \ArrayObject;

    /**
     * @param null|ArrayHelperContract $arrayHelper
     */
    public function setArrayHelper(ArrayHelperContract $arrayHelper): void;

    /**
     * @return null|ArrayHelperContract
     */
    public function getArrayHelper():?ArrayHelperContract;
    /**
     * Gets existing array helper, or makes new one and then returns it
     *
     * @return null|ArrayHelperContract
     */
    public function arrayHelper(): ArrayHelperContract;

    /**
     * @return EventManagerWrapperContract
     * @throws \RuntimeException
     */
    public function createEventManagerWrapper(): EventManagerWrapperContract;

    /**
     * Makes event args to use
     *
     * @param array $params
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params): GenericEventArgsContract;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false): void;

    /**
     * @param string $fieldName
     * @param $value
     * @throws RuntimeException
     */
    public function setField(string $fieldName, $value): void;

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
     * @param EntityContract $entityToBind
     * @param bool $force
     */
    public function bindAssociation(string $assignType = null, string $associationName, EntityContract $entityToBind =null, $force = false): void;

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
    public function ttPrePersist(): void;

    /**
     * Needs extending in a child class to get a validation factory to use
     *
     * @throws \RuntimeException
     */
    public function getValidationFactory();
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     *
     * @param array $values
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws \RuntimeException
     */
    public function validate(array $values, array $rules, array $messages = [], array $customAttributes = []): void;

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
    public function setBindParams(array $bindParams): void;

    /**
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function allowed($nosey = true): bool;

    /**
     * @return NULL|\TempestTools\Crud\Contracts\Orm\Helper\EntityArrayHelperContract
     */
    public function getConfigArrayHelper(): ?EntityArrayHelperContract;

    /**
     * @param EntityArrayHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(EntityArrayHelperContract $configArrayHelper): void;

    /**
     * @return null|string
     */
    public function getLastMode(): ?string;

    /**
     * @param null|string $lastMode
     */
    public function setLastMode(string $lastMode = null): void;

    /**
     * @return EventManagerWrapperContract
     */
    public function getEventManager(): ?EventManagerWrapperContract;

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper): void;

    /**
     * @return mixed
     * @internal param Entity $entity
     * @internal param $names
     * @internal param bool $requireAll
     * @internal param string $permission
     */
    public function getId();

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
     *
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $canDo
     * @param string $target
     * @return bool
     */
    public function permissivePermissionCheck($high, array $low, string $canDo, string $target): bool;

    /**
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $setting
     * @return bool|mixed|null
     */
    public function highLowSettingCheck($high, array $low = null, string $setting);

    /**
     * Common logic for checking if the permissive settings allow something to be don
     *
     * @param array|\ArrayObject $high
     * @param array $low
     * @return bool
     */
    public function permissiveAllowedCheck($high, array $low): bool;

    /**
     * @return bool
     */
    public function isPrePopulated(): bool;

    /**
     * @param bool $prePopulated
     */
    public function setPrePopulated(bool $prePopulated): void;

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param string|null $defaultMode
     * @param ArrayHelperContract|null $defaultArrayHelper
     * @param array|null $defaultPath
     * @param array|null $defaultFallBack
     * @param bool $force
     * @param array $frontEndOptions
     * @param array $slatedToTransform
     * @return array
     */
    public function toArray(string $defaultMode = 'read', ArrayHelperContract $defaultArrayHelper = null, array $defaultPath = null, array $defaultFallBack = null, bool $force = false, array $frontEndOptions = [], array $slatedToTransform = []):array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param $propertyValue
     * @param array $settings
     * @param bool $force
     * @param array $frontEndOptions
     * @param array $slatedToTransform
     * @return mixed
     */
    public function parseToArrayPropertyValue($propertyValue, array $settings = [], bool $force = false, array $frontEndOptions = [], array $slatedToTransform = []);
}