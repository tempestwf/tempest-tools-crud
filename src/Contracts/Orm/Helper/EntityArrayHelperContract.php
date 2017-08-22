<?php

namespace TempestTools\Crud\Contracts\Orm\Helper;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;

interface EntityArrayHelperContract extends ArrayHelperContract
{

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     */
    public function getConfigForField(string $fieldName, string $keyName);

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     */
    public function canChain(EntityContract $entity, string $associationName, string $chainType, bool $nosey = true): bool;


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $assignType
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @return bool
     */
    public function canAssign(EntityContract $entity, string $associationName, string $assignType, array $fieldSettings = null, bool $nosey = true): bool;

    /**
     * @param EntityContract $entity
     * @param bool $nosey
     * @return bool
     */
    public function allowed(EntityContract $entity, $nosey = true): bool;

    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @return bool
     */
    public function checkFastMode(EntityContract $entity, string $fieldName): bool;

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws \RuntimeException
     */
    public function getFieldSettings(string $fieldName, array $params = []): array;

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return mixed
     */
    public function processSetField(EntityContract $entity, array $params);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $nosey
     * @return
     */
    public function enforceRelation(EntityContract $entity, string $fieldName, array $values, array $params = [], array $fieldSettings=NULL, bool $nosey = true);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @param array $params
     * @param array|null $fieldSettings
     * @return array
     */
    public function setToOnAssociation(EntityContract $entity, string $associationName, array $values, array $params = [], array $fieldSettings=NULL):array;


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     */
    public function mutateOnField(EntityContract $entity, string $fieldName, $params, $value, array $fieldSettings = null);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $params
     * @param $value
     * @param array|null $fieldSettings
     * @return
     */
    public function setToOnField(EntityContract $entity, string $fieldName, $params, $value, array $fieldSettings = null);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     */
    public function closureOnField(EntityContract $entity, string $fieldName, array $params, array $fieldSettings = null, bool $noisy = true): bool;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @param array $params
     * @param array|null $fieldSettings
     * @param bool $noisy
     * @return bool
     */
    public function enforceField(EntityContract $entity, string $fieldName, $value, array $params, array $fieldSettings = null, bool $noisy = true): bool;

    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     */
    public function setField(EntityContract $entity, string $fieldName, $value):void;

    /**
     * @param string $verb
     * @param string $property
     * @return string
     */
    public function accessorMethodName(string $verb, string $property): string;

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @return array
     */
    public function processAssociationParams(EntityContract $entity, string $associationName, array $values): array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(EntityContract $entity, string $assignType=null, string $associationName, $force = false):void;

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @param EntityContract $entity
     */
    public function ttPrePersist(EntityContract $entity):void;

    /**
     * @param EntityContract $entity
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(EntityContract $entity, array $fields = []): array;

}