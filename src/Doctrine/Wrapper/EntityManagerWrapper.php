<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 5:54 PM
 */

namespace TempestTools\Scribe\Doctrine\Wrapper;

use Doctrine\ORM\EntityManager;
use TempestTools\Common\Doctrine\Utility\EmTrait;
use TempestTools\Scribe\Contracts\Orm\EntityContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;

/**
 * A wrapper class to provide a universal interface for accessing Doctrine Entity Manager functionality.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class EntityManagerWrapper implements EntityManagerWrapperContract
{
    use EmTrait;

    /**
     * EntityManagerWrapper constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->setEm($entityManager);
    }


    /**
     * Gets the associations for a specific entity class
     * @param string $entityName
     * @return array
     */
    public function getAssociationNames(string $entityName):array
    {
        /** @noinspection NullPointerExceptionInspection */
        $metadata = $this->getEm()->getClassMetadata($entityName);
        return $metadata->getAssociationNames();
    }

    /**
     * Gets the field names for a specific entity class
     * @param string $entityName
     * @return array
     */
    public function getFieldNames(string $entityName):array
    {
        /** @noinspection NullPointerExceptionInspection */
        $metadata = $this->getEm()->getClassMetadata($entityName);
        return $metadata->getFieldNames();
    }

    /**
     * Gets a class name for a specific association for a specific entity class
     * @param string $entityName
     * @param string $fieldName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAssociationTargetClass(string $entityName, string $fieldName):string
    {
        /** @noinspection NullPointerExceptionInspection */
        $metadata = $this->getEm()->getClassMetadata($entityName);
        return $metadata->getAssociationTargetClass($fieldName);
    }

    /**
     * Gets a repository for a specific class
     * @param string $targetClass
     * @return \Doctrine\ORM\EntityRepository|RepositoryContract
     */
    public function getRepository(string $targetClass)
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getEm()->getRepository($targetClass);
    }

    /**
     * Begins a transaction
     */
    public function beginTransaction ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->beginTransaction();
    }

    /**
     * Flushes to the db
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush (EntityContract $entity = null):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->flush($entity);
    }

    /**
     * Rolls back a transaction
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollBack ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->rollBack();
    }

    /**
     * Commits a transaction
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->commit();
    }

    /**
     * Persists an entity
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function persist (EntityContract $entity):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->persist($entity);
    }

    /**
     * Removes an entity
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function remove (EntityContract $entity):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->remove($entity);
    }


}