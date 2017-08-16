<?php
namespace TempestTools\Crud\Contracts;

use TempestTools\Common\Contracts\ArrayHelperContract;

interface DataBindHelperContract {


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     */
    public function init(ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true):void;

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

}
?>