<?php
namespace TempestTools\Crud\Orm\Helper;

use TempestTools\AclMiddleware\Contracts\HasIdContract;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Crud\Constants\RepositoryEventsConstants;
use TempestTools\Crud\Contracts\DataBindHelperContract;
use TempestTools\Crud\Contracts\EntityContract;
use TempestTools\Crud\Contracts\GenericEventArgsContract;
use TempestTools\Crud\Contracts\RepositoryContract;
use TempestTools\Crud\Doctrine\EntityAbstract;
use TempestTools\Crud\Orm\RepositoryTrait;

class DataBindHelper implements DataBindHelperContract
{
    use ErrorConstantsTrait, RepositoryTrait;

    const ERRORS = [
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.',
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows. count = %s, max = %s',
        ],
    ];

    const IGNORE_KEYS = ['assignType'];





    /**
     * DataBindHelper constructor.
     *
     * @param RepositoryContract $repository
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
        $entity->setArrayHelper($this->getRepository()->getArrayHelper());
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
        $entities = $repo->findIn('id', $keys);
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
        $repo = $this->getRepository()->getEm()->getRepository($targetClass);
        $repo->init($this->getRepository()->getArrayHelper(), $this->getRepository()->getTTPath(), $this->getRepository()->getTTFallBack());

        if (!$repo instanceof RepositoryContract) {
            throw new \RuntimeException($this->getErrorFromConstant('wrongTypeOfRepo'));
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
        $evm = $this->getRepository()->getEventManager();
        /** @noinspection NullPointerExceptionInspection */
        $this->getRepository()->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->getRepository()->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'create';

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_CREATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $options = $eventArgs->getArgs()['$options'];
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
     * @param GenericEventArgsContract $eventArgs
     * @return EntityContract
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
        $evm = $this->getRepository()->getEventManager();
        /** @noinspection NullPointerExceptionInspection */
        $this->getRepository()->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->getRepository()->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'update';

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_UPDATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $options = $eventArgs->getArgs()['$options'];
            $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            /** @noinspection NullPointerExceptionInspection */
            /** @noinspection PhpParamsInspection */
            $entities = $this->findEntitiesFromArrayKeys($params, $this);
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
     * @param GenericEventArgsContract $eventArgs
     * @param EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
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
        /** @noinspection NullPointerExceptionInspection */
        $this->getRepository()->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->getRepository()->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'delete';
        $evm = $this->getRepository()->getEventManager();

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
            $entities = $this->findEntitiesFromArrayKeys($params, $this);
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
     * @param GenericEventArgsContract $eventArgs
     * @param EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
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
     * @param EntityContract $entity
     * @param bool $remove
     * @return EntityContract
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function processSingleEntity (GenericEventArgsContract $eventArgs, EntityContract $entity, bool $remove=false): EntityContract
    {
        $entitiesShareConfigs = $eventArgs->getArgs()['entitiesShareConfigs'];
        if ($entitiesShareConfigs === true) {
            if (isset($eventArgs->getArgs()['sharedConfig'])) {
                $sharedConfig = $eventArgs->getArgs()['sharedConfig'];
                $entity->setConfigArrayHelper($sharedConfig);
            }
        }
        $entity->init($eventArgs->getArgs()['action'] , $this->getRepository()->getArrayHelper(), $this->getRepository()->getTTPath(), $this->getRepository()->getTTFallBack());
        if ($entitiesShareConfigs === true) {
            $eventArgs->getArgs()['sharedConfig'] = $entity->getConfigArrayHelper();
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->bind($entity, $eventArgs->getArgs()['params']);
        if ($remove === true) {
            $this->getRepository()->getEm()->remove($entity);
        } else {
            $this->getRepository()->getEm()->persist($entity);
        }
        return $entity;
    }


    /**
     * @param array $values
     * @param array $options
     * @param array $optionOverrides
     * @throws \RuntimeException
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
                throw new \RuntimeException(sprintf($this->getErrorFromConstant('moreRowsRequestedThanBatchMax')['message'], $count, $maxBatch));
            }
        }
    }



}
?>