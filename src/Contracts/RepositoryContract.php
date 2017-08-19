<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 6:12 PM
 */

namespace TempestTools\Crud\Contracts;

use \Exception;

use TempestTools\Common\Contracts\ArrayHelperContract;


interface RepositoryContract
{

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false);

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(array $params = [], array $frontEndOptions = [], array $optionOverrides = []): array;


    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @param string $fieldName
     * @param array $values
     * @return mixed
     */
    public function findIn(string $fieldName, array $values):array;

    public function getTTConfig(): array;

    /**
     * @return array|NULL
     */
    public function getOptions(): ?array;

    /**
     * @param array|NULL $options
     */
    public function setOptions($options): void;

    /**
     * @return null|DataBindHelperContract
     */
    public function getDataBindHelper(): ?DataBindHelperContract;

    /**
     * @param DataBindHelperContract $dataBindHelper
     */
    public function setDataBindHelper(DataBindHelperContract $dataBindHelper): void;


    /**
     * @return NULL|QueryBuilderHelperContract
     */
    public function getConfigArrayHelper():?QueryBuilderHelperContract;

    /**
     * @param QueryBuilderHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(QueryBuilderHelperContract $configArrayHelper): void;

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

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Common logic for checking if the permissive settings allow something to be don
     *
     * @param array|\ArrayObject $high
     * @param array $low
     * @return bool
     */
    public function permissiveAllowedCheck($high, array $low): bool;

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
    public function coreInit(ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = true, string $mode = null): bool;

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
     */
    public function getEventManager(): EventManagerWrapperContract;

    /**
     * @param EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper): void;

    /**
     * @return EntityManagerWrapperContract
     */
    public function getEm(): EntityManagerWrapperContract;

    /**
     * @param EntityManagerWrapperContract $em
     */
    public function setEm(EntityManagerWrapperContract $em): void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Makes event args to use
     *
     * @param array $params
     * @param array $options
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params, array $options = [], array $optionOverrides = [], array $frontEndOptions = []): GenericEventArgsContract;

    /**
     * @return string
     */
    public function getClassNameBase():string;

    /**
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \RuntimeException
     */
    public function createQueryWrapper(string $entityAlias=null):QueryBuilderWrapperContract;

    /**
     * @return string
     */
    public function getEntityAlias():string;
}