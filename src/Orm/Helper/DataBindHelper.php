<?php
namespace TempestTools\Crud\Orm\Helper;

use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Contracts\DataBindHelperContract;
use TempestTools\Crud\Contracts\EntityContract;
use TempestTools\Crud\Contracts\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\RepositoryContract;
use TempestTools\Crud\Doctrine\EntityAbstract;
use TempestTools\Common\Contracts\ArrayHelperContract;

class DataBindHelper implements DataBindHelperContract {
    use ArrayHelperTrait, TTConfigTrait, ErrorConstantsTrait;

    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on DataBindHelper.',
        ],
    ];

    const IGNORE_KEYS = ['assignType'];

    /**
     * @var EntityManagerWrapperContract $em
     */
    protected $em;

    /**
     * DataBindHelper constructor.
     *
     * @param EntityManagerWrapperContract $entityManager
     */
    public function __construct(EntityManagerWrapperContract $entityManager)
    {
        $this->setEm($entityManager);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true): void
    {
        if ($arrayHelper !== NULL && ($force === true || $this->getArrayHelper() === NULL)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== null && ($force === true || $this->getTTPath() === null || $path !== $this->getTTPath())) {
            $this->setTTPath($path);
        }

        if ($fallBack !== null && ($force === true || $this->getTTFallBack() === null || $fallBack !== $this->getTTFallBack() )) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }
    }

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return EntityContract
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function bind(EntityContract $entity, array $params): EntityContract
    {
        /** @noinspection NullPointerExceptionInspection */
        $entity->allowed();
        $entity->setArrayHelper($this->getArrayHelper());
        $entity->setBindParams($params);
        /** @noinspection NullPointerExceptionInspection */
        $entityName = get_class($entity);
        /** @noinspection NullPointerExceptionInspection */
        $associateNames = $this->getEm()->getAssociationNames($entityName);
        foreach ($params as $fieldName => $value) {
            if (in_array($fieldName, $associateNames, true)) {

                /** @noinspection NullPointerExceptionInspection */
                $targetClass = $this->getEm()->getAssociationTargetClass($entityName, $fieldName);
                $value = $this->fixScalarAssociationValue($value);
                $this->bindAssociation($entity, $fieldName, $value, $targetClass);
            } else if (!in_array($fieldName, static::IGNORE_KEYS, true)) {
                $entity->setField($fieldName, $value);
            }
        }
        return $entity;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function fixScalarAssociationValue($value):array {
        $return = $value !== null && is_scalar($value) ? [
            'read' => [
                $value => [
                    'assignType' => 'set'
                ]
            ]
        ] : $value;
        return $return;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     */
    public function bindAssociation(EntityContract $entity, string $associationName, array $params = NULL, string $targetClass): void
    {
        $repo = $this->getRepoForRelation($targetClass);
        $chainOverrides = ['transaction'=>false, 'flush'=>false, 'batchMax'=>null];
        if ($params !== NULL) {
            foreach ($params as $chainType => $paramsForEntities) {
                $paramsForEntities = $this->prepareAssociationParams($entity, $associationName, $paramsForEntities);
                $foundEntities = $this->processChaining($chainType, $paramsForEntities, $chainOverrides, $repo);

                if ($foundEntities !== null) {
                    $this->bindEntities($foundEntities, $entity, $associationName);
                }
            }
        } else {
            $entity->bindAssociation('set', $associationName, NULL, true);
        }
    }

    /**
     * @param array $entities
     * @param EntityContract $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities (array $entities, EntityContract $targetEntity, string $associationName): void
    {
        foreach ($entities as $foundEntity) {
            $params = $foundEntity->getBindParams();
            $assignType = $params['assignType'] ?? null;
            $targetEntity->bindAssociation($assignType, $associationName, $foundEntity);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $chainType
     * @param array $params
     * @param array $chainOverrides
     * @param RepositoryContract $repo
     * @return array|null
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    protected function processChaining (string $chainType, array $params, array $chainOverrides, RepositoryContract $repo):?array {
        $foundEntities = null;
        /** @var EntityAbstract $foundEntity */
        if ($chainType !== null) {
            switch ($chainType) {
                case 'create':
                    $foundEntities = $repo->create($params, $chainOverrides);
                    break;
                case 'read':
                    $foundEntities = $this->findEntitiesFromArrayKeys($params, $repo);
                    break;
                case 'update':
                    $foundEntities = $repo->update($params, $chainOverrides);
                    break;
                case 'delete':
                    $foundEntities = $repo->delete($params, $chainOverrides);
                    break;
            }
        }
        return $foundEntities;
    }
    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $paramsForEntities
     * @return array
     * @throws \RuntimeException
     */
    protected function prepareAssociationParams (EntityContract $entity, string $associationName, array $paramsForEntities):array {
        /** @var array $paramsForEntities */
        foreach ($paramsForEntities as $key=>$paramsForEntity) {
            $paramsForEntities[$key] = $entity->processAssociationParams($associationName, $paramsForEntity);
        }
        return $paramsForEntities;
    }

    /**
     * @param array $array
     * @param RepositoryContract $repo
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array, RepositoryContract $repo):array {
        $keys = array_keys($array);
        /** @var EntityContract|HasIdContract[] $entities */
        $entities = $repo->findIn('id', $keys)->getQuery()->getResult();
        /** @var EntityContract|HasIdContract $entity */
        foreach ($entities as $entity) {
            $entity->setBindParams($array[$entity->getId()]);
        }
        return $entities;
    }

    /**
     * @param string $targetClass
     * @throws \RuntimeException
     * @return RepositoryContract
     */
    public function getRepoForRelation(string $targetClass):RepositoryContract {
        /** @var RepositoryContract $repo */
        /** @noinspection NullPointerExceptionInspection */
        $repo = $this->getEm()->getRepository($targetClass);
        $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack(), false);

        // TODO: Use a contract here instead
        if (!$repo instanceof RepositoryContract) {
            throw new \RuntimeException($this->getErrorFromConstant('wrongTypeOfRepo'));
        }
        return $repo;
    }

    /**
     * @param EntityManagerWrapperContract $em
     */
    public function setEm(EntityManagerWrapperContract $em): void
    {
        $this->em = $em;
    }

    /**
     * @return EntityManagerWrapperContract
     */
    public function getEm(): ?EntityManagerWrapperContract
    {
        return $this->em;
    }
}
?>