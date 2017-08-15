<?php
namespace TempestTools\Crud\Contracts;

use Doctrine\ORM\EntityManager;
use TempestTools\Common\Contracts\ArrayHelperContract as ArrayHelperContract;
use TempestTools\Crud\Doctrine\RepositoryAbstract;

interface DataBindHelperContract {
    public function __construct(EntityManager $entityManager);


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     */
    public function init(ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true):void;

    /**
     * @param EntityHelperContract $entity
     * @param array $params
     * @return EntityHelperContract
     */
    public function bind(EntityHelperContract $entity, array $params): EntityHelperContract;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param EntityHelperContract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     */
    public function bindAssociation(EntityHelperContract $entity, string $associationName, array $params, string $targetClass):void;

    /**
     * @param array $entities
     * @param EntityHelperContract $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities (array $entities, EntityHelperContract $targetEntity, string $associationName);

    /**
     * @param array $array
     * @param RepositoryAbstract $repo
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array, RepositoryAbstract $repo):array;
    /**
     * @param string $targetClass
     * @throws \RuntimeException
     * @return RepositoryAbstract
     */
    public function getRepoForRelation(string $targetClass):RepositoryAbstract;

}
?>