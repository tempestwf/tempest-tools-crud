<?php
namespace TempestTools\Crud\Orm\Helper;

use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Crud\Constants\RepositoryEventsConstants;
use TempestTools\Crud\Contracts\Orm\Helper\DataBindHelperContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Doctrine\EntityAbstract;
use TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException;
use TempestTools\Crud\Orm\Utility\RepositoryTrait;

class DataBindHelper implements DataBindHelperContract
{
    use RepositoryTrait;

    const IGNORE_KEYS = ['assignType'];

    /**
     * DataBindHelper constructor.
     *
     * @param \TempestTools\Crud\Contracts\Orm\RepositoryContract $repository
     */
    public function __construct(RepositoryContract $repository)
    {
        $this->setRepository($repository);
    }


    /**
     * Makes sure the repo is ready to run
     *
     * @param GenericEventArgsContract $eventArgs
     * @internal param array $optionOverrides
     */
    protected function start(GenericEventArgsContract $eventArgs):void
    {
        $this->getRepository()->getEventManager()->dispatchEvent(RepositoryEventsConstants::PRE_START, $eventArgs);
        $options = $eventArgs->getArgs()['options'];
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $this->getRepository()->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'transaction');

        if ($transaction !== false) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getRepository()->getEm()->beginTransaction();
        }
    }

    /**
     * Makes sure every wraps up
     *
     * @param bool $failure
     * @param GenericEventArgsContract $eventArgs
     * @internal param array $optionOverrides
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \RuntimeException
     */
    protected function stop($failure = false, GenericEventArgsContract $eventArgs):void
    {
        $this->getRepository()->getEventManager()->dispatchEvent(RepositoryEventsConstants::PRE_STOP, $eventArgs);
        $options = $eventArgs->getArgs()['options'];
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $this->getRepository()->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'transaction');

        /** @noinspection NullPointerExceptionInspection */
        $flush = $this->getRepository()->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'flush');


        if ($failure === false && $flush === true) {
            /** @noinspection NullPointerExceptionInspection */
            $this->getRepository()->getEm()->flush();
        }

        if (
            $transaction !== false
        ) {
            if ($failure === true) {
                /** @noinspection NullPointerExceptionInspection */
                $this->getRepository()->getEm()->rollBack();
            } else {
                /** @noinspection NullPointerExceptionInspection */
                $this->getRepository()->getEm()->commit();
            }
        }
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param array $params
     * @return \TempestTools\Crud\Contracts\Orm\EntityContract
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
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
        $entity->setBindParams($params);
        /** @noinspection NullPointerExceptionInspection */
        $entityName = get_class($entity);
        /** @noinspection NullPointerExceptionInspection */
        $associateNames = $this->getRepository()->getEm()->getAssociationNames($entityName);
        foreach ($params as $fieldName => $value) {
            if (in_array($fieldName, $associateNames, true)) {

                /** @noinspection NullPointerExceptionInspection */
                $targetClass = $this->getRepository()->getEm()->getAssociationTargetClass($entityName, $fieldName);
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
        $return = is_scalar($value) ? [
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
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
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
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $targetEntity
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
     * @param \TempestTools\Crud\Contracts\Orm\RepositoryContract $repo
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
                    $foundEntities = $repo->findEntitiesFromArrayKeys($params);
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
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
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
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array):array {
        $keys = array_keys($array);
        /** @var \TempestTools\Crud\Contracts\Orm\EntityContract|HasIdContract[] $entities */
        $entities = $this->getRepository()->findIn('id', $keys);
        /** @var \TempestTools\Crud\Contracts\Orm\EntityContract|HasIdContract $entity */
        foreach ($entities as $entity) {
            $entity->setBindParams($array[$entity->getId()]);
        }
        return $entities;
    }

    /**
     * @param string $targetClass
     * @throws DataBindHelperException
     * @return RepositoryContract
     * @throws \RuntimeException
     */
    public function getRepoForRelation(string $targetClass):RepositoryContract {
        /** @var \TempestTools\Crud\Contracts\Orm\RepositoryContract $repo */
        /** @noinspection NullPointerExceptionInspection */
        $myRepo = $this->getRepository();
        $repo = $myRepo->getEm()->getRepository($targetClass);
        $repo->init($myRepo->getArrayHelper(), $myRepo->getTTPath(), $myRepo->getTTFallBack());

        if (!$repo instanceof RepositoryContract) {
            throw DataBindHelperException::wrongTypeOfRepo();
        }
        return $repo;
    }


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
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {
        $repo = $this->getRepository();
        $evm = $repo->getEventManager();
        /** @noinspection NullPointerExceptionInspection */
        $repo->getArrayHelper()->wrapArray($params);
        $eventArgs = $repo->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'create';

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_CREATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $options = $eventArgs->getArgs()['options'];
            $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            foreach ($params as $batchParams) {
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doCreate($eventArgs);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_CREATE_BATCH, $eventArgs);
        } catch (\Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgsContract $eventArgs
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     */
    protected function doCreate (GenericEventArgsContract $eventArgs):void
    {
        $evm = $this->getRepository()->getEventManager();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_CREATE, $eventArgs);
        $result = $this->doCreateSingle($eventArgs);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_CREATE, $eventArgs);
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract $eventArgs
     * @return \TempestTools\Crud\Contracts\Orm\EntityContract
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function doCreateSingle(GenericEventArgsContract $eventArgs): EntityContract
    {
        $className = $this->getRepository()->getClassNameBase();
        /** @var EntityAbstract $entity */
        return $this->processSingleEntity($eventArgs, new $className());
    }

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
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {
        $repo = $this->getRepository();
        $evm = $repo->getEventManager();
        /** @noinspection NullPointerExceptionInspection */
        $repo->getArrayHelper()->wrapArray($params);
        $eventArgs = $repo->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'update';

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_UPDATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $options = $eventArgs->getArgs()['options'];
            $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            /** @noinspection NullPointerExceptionInspection */
            /** @noinspection PhpParamsInspection */
            $entities = $this->findEntitiesFromArrayKeys($params);
            /** @var EntityAbstract $entity */
            foreach ($entities as $entity) {
                $batchParams = $entity->getBindParams();
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doUpdate($eventArgs, $entity);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_UPDATE_BATCH, $eventArgs);
        } catch (\Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract $eventArgs
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     */
    protected function doUpdate (GenericEventArgsContract $eventArgs, EntityContract $entity):void
    {
        $evm = $this->getRepository()->getEventManager();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_UPDATE, $eventArgs);
        $result = $this->processSingleEntity($eventArgs, $entity);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_UPDATE, $eventArgs);
    }


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
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {
        $repo = $this->getRepository();
        /** @noinspection NullPointerExceptionInspection */
        $repo->getArrayHelper()->wrapArray($params);
        $eventArgs = $repo->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'delete';
        $evm = $repo->getEventManager();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_DELETE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $options = $eventArgs->getArgs()['$options'];
            $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            /** @noinspection NullPointerExceptionInspection */
            /** @noinspection PhpParamsInspection */
            $entities = $this->findEntitiesFromArrayKeys($params);
            /** @var EntityAbstract $entity */
            foreach ($entities as $entity) {
                $batchParams = $entity->getBindParams();
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doDelete($eventArgs, $entity);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_DELETE_BATCH, $eventArgs);
        } catch (\Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract $eventArgs
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     */
    protected function doDelete (GenericEventArgsContract $eventArgs, EntityContract $entity):void
    {
        $evm = $this->getRepository()->getEventManager();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_DELETE, $eventArgs);
        $result = $this->processSingleEntity($eventArgs, $entity, true);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_DELETE, $eventArgs);
    }

    /**
     * @param GenericEventArgsContract $eventArgs
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $entity
     * @param bool $remove
     * @return \TempestTools\Crud\Contracts\Orm\EntityContract
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\DataBindHelperException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function processSingleEntity (GenericEventArgsContract $eventArgs, EntityContract $entity, bool $remove=false): EntityContract
    {
        $repo = $this->getRepository();
        $em = $repo->getEm();
        $entitiesShareConfigs = $eventArgs->getArgs()['entitiesShareConfigs'];
        if ($entitiesShareConfigs === true) {
            if (isset($eventArgs->getArgs()['sharedConfig'])) {
                $sharedConfig = $eventArgs->getArgs()['sharedConfig'];
                $entity->setConfigArrayHelper($sharedConfig);
            }
        }
        $entity->init($eventArgs->getArgs()['action'] , $repo->getArrayHelper(), $repo->getTTPath(), $repo->getTTFallBack());
        if ($entitiesShareConfigs === true) {
            $eventArgs->getArgs()['sharedConfig'] = $entity->getConfigArrayHelper();
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->bind($entity, $eventArgs->getArgs()['params']);
        if ($remove === true) {
            $em->remove($entity);
        } else {
            $em->persist($entity);
        }
        return $entity;
    }


    /**
     * @param array $values
     * @param array $options
     * @param array $optionOverrides
     * @throws DataBindHelperException
     */
    protected function checkBatchMax(array $values, array $options, array $optionOverrides):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $maxBatch = $this->getRepository()->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'batchMax');

        if ($maxBatch !== NULL) {
            $count = count($values, COUNT_RECURSIVE);

            if ($count > $maxBatch) {
                throw DataBindHelperException::moreRowsRequestedThanBatchMax($count, $maxBatch);
            }
        }
    }



}
?>