<?php
namespace TempestTools\Crud\Contracts;


interface DataBindHelperContract {

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return EntityContract
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
     * @param RepositoryContract $repo
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array, RepositoryContract $repo):array;
    /**
     * @param string $targetClass
     * @throws \RuntimeException
     * @return RepositoryContract
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