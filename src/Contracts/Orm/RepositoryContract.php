<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 6:12 PM
 */

namespace TempestTools\Scribe\Contracts\Orm;

use \Exception;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Scribe\Contracts\HasTTConfig;
use TempestTools\Scribe\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\EventManagerWrapperContract;
use TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Scribe\Contracts\Orm\Helper\DataBindHelperContract;
use TempestTools\Scribe\Contracts\Orm\Helper\QueryBuilderHelperContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface RepositoryContract extends HasTTConfig
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
     * Subscribes to the available events that are present on the class
     * @return array
     */
    public function getSubscribedEvents():array;

    /**
     * @param string $fieldName
     * @param array $values
     * @return mixed
     */
    public function findIn(string $fieldName, array $values): array;

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
     * @return \TempestTools\Scribe\Contracts\Orm\Wrapper\EventManagerWrapperContract
     */
    public function getEventManager(): ?EventManagerWrapperContract;

    /**
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper): void;

    /**
     * @return string
     */
    public function getEntityNameBase(): string;

    /**
     * @return \TempestTools\Scribe\Contracts\Orm\Wrapper\EntityManagerWrapperContract
     */
    public function getEm(): ?EntityManagerWrapperContract;

    /**
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\EntityManagerWrapperContract $em
     */
    public function setEm(EntityManagerWrapperContract $em): void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Makes event args to use
     *
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params, array $optionOverrides = [], array $frontEndOptions = []): GenericEventArgsContract;

    /**
     * @return string
     */
    public function getClassNameBase(): string;

    /**
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \RuntimeException
     */
    public function createQueryWrapper(string $entityAlias = null): QueryBuilderWrapperContract;

    /**
     * @return string
     */
    public function getEntityAlias(): string;

    /**
     * @return \TempestTools\Scribe\Contracts\Orm\Wrapper\EventManagerWrapperContract
     * @throws \RuntimeException
     */
    public function createEventManagerWrapper(): EventManagerWrapperContract;

    /**
     * @return EntityManagerWrapperContract
     */
    public function createEntityManagerWrapper():EntityManagerWrapperContract;

    /**
     * @param array $array
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array):array;

    /**
     * @param array $params
     * @param array $gathered
     * @return array
     * @throws \InvalidArgumentException
     */
    public function gatherPrePopulateEntityIds (array $params, array $gathered=[]):array;

    /**
     *
     */
    public function clearPrePopulatedEntities ():void;


}