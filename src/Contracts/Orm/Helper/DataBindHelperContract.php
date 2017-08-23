<?php
namespace TempestTools\Crud\Contracts\Orm\Helper;


use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;

interface DataBindHelperContract {

    /**
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param array $params
     * @return \TempestTools\Crud\Contracts\Orm\EntityContract
     */
    public function bind(EntityContract $entity, array $params): EntityContract;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     */
    public function bindAssociation(EntityContract $entity, string $associationName, array $params, string $targetClass):void;

    /**
     * @param array $entities
     * @param EntityContract $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities (array $entities, EntityContract $targetEntity, string $associationName);

    /**
     * @param array $array
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array):array;
    /**
     * @param string $targetClass
     * @throws \RuntimeException
     * @return \TempestTools\Crud\Contracts\Orm\RepositoryContract
     */
    public function getRepoForRelation(string $targetClass):RepositoryContract;

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
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

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
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

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
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

}
?>