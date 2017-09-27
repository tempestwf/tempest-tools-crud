<?php

namespace TempestTools\Crud\Contracts\Orm\Helper;


use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToAssociationPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToFieldPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\PrePersistEntityBuilderContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;

interface EntityArrayHelperContract extends ArrayHelperContract
{

    /**
     * @param string $verb
     * @param string $property
     * @return string
     */
    public function accessorMethodName(string $verb, string $property): string;

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     * @throws RuntimeException
     */
    public function getConfigForField(string $fieldName, string $keyName);/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain(EntityContract $entity, string $associationName, string $chainType, bool $nosey = true): bool;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $assignType
     * @param array $fieldSettings
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function canAssign(EntityContract $entity, string $associationName, string $assignType = null, array $fieldSettings = null, bool $nosey = true): bool;

    /**
     * @param EntityContract $entity
     * @param bool $nosey
     * @return bool
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function allowed(EntityContract $entity, $nosey = true): bool;

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws RuntimeException
     */
    public function getFieldSettings(string $fieldName, array $params = []): array;

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField(EntityContract $entity, array $params);

    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @throws \RuntimeException
     */
    public function setField(EntityContract $entity, string $fieldName, $value): void;

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(EntityContract $entity, string $associationName, array $values): array;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entityToBind
     * @param bool $force
     */
    public function bindAssociation(EntityContract $entity, string $assignType = null, string $associationName, EntityContract $entityToBind=null, $force = false): void;

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    public function ttPrePersist(EntityContract $entity): void;

    /**
     * @param EntityContract $entity
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(EntityContract $entity, array $fields = []): array;

    /**
     * @return ArrayToFieldPropertyBuilderContract
     */
    public function getArrayToFieldPropertyBuilder(): ArrayToFieldPropertyBuilderContract;

    /**
     * @param ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder
     */
    public function setArrayToFieldPropertyBuilder(ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder);

    /**
     * @return ArrayToAssociationPropertyBuilderContract
     */
    public function getArrayToAssociationPropertyBuilder(): ArrayToAssociationPropertyBuilderContract;

    /**
     * @param ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder
     */
    public function setArrayToAssociationPropertyBuilder(ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder);

    /**
     * @return PrePersistEntityBuilderContract
     */
    public function getPrePersistEntityBuilder(): PrePersistEntityBuilderContract;

    /**
     * @param PrePersistEntityBuilderContract $prePersistEntityBuilder
     */
    public function setPrePersistEntityBuilder(PrePersistEntityBuilderContract $prePersistEntityBuilder);

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param array $settings
     * @param mixed $slatedToTransform
     * @return array
     */
    public function toArray(EntityContract $entity, array $settings, $slatedToTransform = null):array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param array $settings
     * @param mixed $slatedToTransform
     * @return array
     */
    public function toArrayCore(EntityContract $entity, array $settings, $slatedToTransform = null):array;

}