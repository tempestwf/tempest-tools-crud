<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 5:54 PM
 */

namespace TempestTools\Crud\Doctrine\Wrapper;

use Doctrine\ORM\EntityManager;
use TempestTools\Common\Doctrine\Utility\EmTrait;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;

class EntityManagerWrapper implements EntityManagerWrapperContract
{
    use EmTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEm($entityManager);
    }


    /**
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
     * @param string $targetClass
     * @return \Doctrine\ORM\EntityRepository|RepositoryContract
     */
    public function getRepository(string $targetClass)
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getEm()->getRepository($targetClass);
    }

    /**
     *
     */
    public function beginTransaction ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->beginTransaction();
    }

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush (EntityContract $entity = null):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->flush($entity);
    }

    /**
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollBack ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->rollBack();
    }

    /**
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->getConnection()->commit();
    }

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function persist (EntityContract $entity):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->persist($entity);
    }

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function remove (EntityContract $entity):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEm()->remove($entity);
    }


}