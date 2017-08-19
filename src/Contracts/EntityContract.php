<?php

namespace TempestTools\Crud\Contracts;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use Doctrine\ORM\Mapping as ORM;

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
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = true):void;


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
     * @param string $verb
     * @param string $property
     * @return string
     */
    public function accessorMethodName(string $verb, string $property): string;

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
     * @param EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper):void;

}