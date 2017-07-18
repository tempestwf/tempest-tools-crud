<?php
namespace TempestTools\Crud\Contracts;

use Doctrine\ORM\EntityManager;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;
use TempestTools\Crud\Doctrine\RepositoryAbstract;

interface DataBindHelper {
    public function __construct(EntityManager $entityManager);


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @return
     */
    public function init(ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true);

    /**
     * @param Entity $entity
     * @param array $params
     * @return Entity
     */
    public function bind(Entity $entity, array $params): Entity;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Entity $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     * @return
     */
    public function bindAssociation(Entity $entity, string $associationName, array $params, string $targetClass);

    /**
     * @param array $entities
     * @param Entity $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities (array $entities, Entity $targetEntity, string $associationName);

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