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
use TempestTools\Crud\Contracts\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\RepositoryContract;

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


}