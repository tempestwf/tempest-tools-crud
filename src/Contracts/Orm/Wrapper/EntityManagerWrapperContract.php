<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 8:46 PM
 */

namespace TempestTools\Crud\Contracts\Orm\Wrapper;



use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;

interface EntityManagerWrapperContract
{
    /**
     * @param string $entityName
     * @return array
     */
    public function getAssociationNames(string $entityName): array;

    /**
     * @param string $entityName
     * @param string $fieldName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAssociationTargetClass(string $entityName, string $fieldName): string;

    /**
     * @param string $targetClass
     * @return \Doctrine\ORM\EntityRepository|RepositoryContract
     */
    public function getRepository(string $targetClass);

    /**
     *
     */
    public function beginTransaction(): void;

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(EntityContract $entity = null): void;

    /**
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollBack(): void;

    /**
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit(): void;

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function persist(EntityContract $entity): void;

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function remove(EntityContract $entity): void;

    /**
     * @param string $entityName
     * @return array
     */
    public function getFieldNames(string $entityName):array;
}