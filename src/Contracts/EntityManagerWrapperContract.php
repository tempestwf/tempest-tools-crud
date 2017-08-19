<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 6:12 PM
 */

namespace TempestTools\Crud\Contracts;



interface EntityManagerWrapperContract
{

    /**
     * @param string $entityName
     * @return array
     */
    public function getAssociationNames(string $entityName):array;

    /**
     * @param string $entityName
     * @param string $fieldName
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAssociationTargetClass(string $entityName, string $fieldName):string;

    /**
     * @param string $targetClass
     * @return \Doctrine\ORM\EntityRepository|RepositoryContract
     */
    public function getRepository(string $targetClass);

    /**
     *
     */
    public function beginTransaction ():void;

    /**
     * @param null $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush ($entity = null):void;

    /**
     *
     */
    public function rollBack ():void;

    /**
     *
     */
    public function commit ():void;

    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function persist (EntityContract $entity):void;
    /**
     * @param EntityContract $entity
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function remove (EntityContract $entity):void;
}