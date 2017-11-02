<?php
namespace TempestTools\Crud\Contracts\Orm\Helper;


use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface DataBindHelperContract
{

    /**
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param array $params
     * @return \TempestTools\Crud\Contracts\Orm\EntityContract
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function bind(EntityContract $entity, array $params): EntityContract;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     */
    public function bindAssociation(EntityContract $entity, string $associationName, array $params = null, string $targetClass): void;

    /**
     * @param array $entities
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities(array $entities, EntityContract $targetEntity, string $associationName): void;

    /**
     * @param array $array
     * @return array
     */
    public function findEntitiesFromArrayKeys(array $array): array;

    /**
     * @param string $targetClass
     * @throws DataBindHelperException
     * @return RepositoryContract
     * @throws \RuntimeException
     */
    public function getRepoForRelation(string $targetClass): RepositoryContract;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return array
     * @throws \Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return array
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return array
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions = []): array;

    /**
     * @return \TempestTools\Crud\Contracts\Orm\RepositoryContract
     */
    public function getRepository(): RepositoryContract;

    /**
     * @param RepositoryContract $repository
     */
    public function setRepository(RepositoryContract $repository): void;

    /**
     * @param array $params
     * @param array $gathered
     * @return array
     */
    public function gatherPrePopulateEntityIds (array $params, array $gathered=[]):array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $params
     * @param array $options
     * @param array $optionOverrides
     * @param string $action
     */
    public function prePopulateEntities(array $params, array $options, array $optionOverrides, string $action):void;

    /**
     *
     */
    public function clearPrePopulatedEntities():void;
}
?>