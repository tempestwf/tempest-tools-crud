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

    const PRE_POPULATED_ENTITIES_KEY = 'prePopulatedEntities';

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
     * @throws \RuntimeException
     */
    protected function start(GenericEventArgsContract $eventArgs):void
    {
        $repo = $this->getRepository();
        /** @noinspection NullPointerExceptionInspection */
        $repo->getEventManager()->dispatchEvent(RepositoryEventsConstants::PRE_START, $eventArgs);
        $options = $eventArgs->getArgs()['options'];
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $repo->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'transaction');

        if ($transaction !== false) {
            /** @noinspection NullPointerExceptionInspection */
            $repo->getEm()->beginTransaction();
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
        $repo = $this->getRepository();
        $em = $repo->getEm();
        $arrayHelper = $repo->getArrayHelper();
        $evm = $repo->getEventManager();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_STOP, $eventArgs);
        $options = $eventArgs->getArgs()['options'];
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $arrayHelper->findSetting([
            $options,
            $optionOverrides,
        ], 'transaction');

        /** @noinspection NullPointerExceptionInspection */
        $flush = $arrayHelper->findSetting([
            $options,
            $optionOverrides,
        ], 'flush');

        /** @noinspection NullPointerExceptionInspection */
        $clearPrePopulatedEntitiesOnFlush = $arrayHelper->findSetting([
            $options,
            $optionOverrides,
        ], 'clearPrePopulatedEntitiesOnFlush');


        if ($failure === false && $flush === true) {
            /** @noinspection NullPointerExceptionInspection */
            $em->flush();
            if ($clearPrePopulatedEntitiesOnFlush) {
                $this->clearPrePopulatedEntities();
            }
        }

        if (
            $transaction !== false
        ) {
            if ($failure === true) {
                /** @noinspection NullPointerExceptionInspection */
                $em->rollBack();
            } else {
                /** @noinspection NullPointerExceptionInspection */
                $em->commit();
            }
        }
    }

    public function clearPrePopulatedEntities():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getRepository()->getArrayHelper()->getArray()[static::PRE_POPULATED_ENTITIES_KEY] = null;
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
        $fieldNames = $this->getRepository()->getEm()->getFieldNames($entityName);
        foreach ($params as $fieldName => $value) {
            if (in_array($fieldName, $associateNames, true)) {

                /** @noinspection NullPointerExceptionInspection */
                $targetClass = $this->getRepository()->getEm()->getAssociationTargetClass($entityName, $fieldName);
                $value = $this->fixScalarAssociationValue($value);
                $this->bindAssociation($entity, $fieldName, $value, $targetClass);
            } else if (!in_array($fieldName, static::IGNORE_KEYS, true)) {
                if (!in_array($fieldName, $fieldNames, true)) {
                    throw DataBindHelperException::propertyNotAField($fieldName, get_class($entity));
                }
                $entity->setField($fieldName, $value);
            }
        }
        return $entity;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function fixScalarAssociationValue($value):?array
    {
        $return = is_scalar($value) && $value !== null? [
            'read' => [
                $value => [
                    'assignType' => 'set',
                ],
            ],
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
        $chainOverrides = ['transaction'=>false, 'flush'=>false, 'batchMax'=>null, 'prePopulateEntities'=>false];
        if ($params !== NULL) {
            foreach ($params as $chainType => $paramsForEntities) {
                $paramsForEntities = $this->prepareAssociationParams($entity, $associationName, $paramsForEntities);
                $foundEntities = $this->processChaining($chainType, $paramsForEntities, $chainOverrides, $repo);

                if ($foundEntities !== null) {
                    $this->bindEntities($foundEntities, $entity, $associationName);
                }
            }
        } else {
            $this->bindEntities(null, $entity, $associationName);
        }
    }

    /**
     * @param array $entities
     * @param \TempestTools\Crud\Contracts\Orm\EntityContract $targetEntity
     * @param string $associationName
     * @throws \RuntimeException
     */
    public function bindEntities (array $entities=null, EntityContract $targetEntity, string $associationName): void
    {
        if ($entities !== null) {
            foreach ($entities as $foundEntity) {
                $params = $foundEntity->getBindParams();
                $assignType = $params['assignType'] ?? null;
                $targetEntity->bindAssociation($assignType, $associationName, $foundEntity);
            }
        } else {
            $targetEntity->bindAssociation('setNull', $associationName, null);
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
        [$keys, $entities] = $this->retrievePrePopulatedEntities($array);
        /** @var \TempestTools\Crud\Contracts\Orm\EntityContract|HasIdContract[] $entities */
        $foundEntities = $keys !== []?$this->getRepository()->findIn('id', $keys):[];
        $entities = array_merge($entities, $foundEntities);
        /** @var \TempestTools\Crud\Contracts\Orm\EntityContract|HasIdContract $entity */
        foreach ($entities as $entity) {
            $entity->setBindParams($array[$entity->getId()]);
        }
        return $entities;
    }

    /**
     * @param array $array
     * @return array
     */
    protected function retrievePrePopulatedEntities(array $array):array
    {
        $keys = array_keys($array);
        $repo = $this->getRepository();
        $className = $repo->getClassNameBase();

        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $array = $repo->getArrayHelper()->getArray()[static::PRE_POPULATED_ENTITIES_KEY][$className] ?? null;

        if ($array !== null) {
            $entities = [];
            $remainingKeys = [];
            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $entities[] = $array[$key];
                } else {
                    $remainingKeys[] = $key;
                }
            }
            return [$remainingKeys, $entities];
        }

        return [$keys, []];


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
     * @param array $gathered
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function gatherPrePopulateEntityIds (array $params, array $gathered=[]):array
    {
        $repo = $this->getRepository();
        $em = $repo->getEm();
        $entityName = $repo->getEntityNameBase();

        // Loops through associations, gather ids referenced, and recursively do the same into the repos for those associations
        $associateNames = $em->getAssociationNames($entityName);
        foreach ($params as $param) {
            foreach ($associateNames as $associateName) {
                if (isset($param[$associateName])) {
                    $found = [];
                    $associationInfo = $param[$associateName];
                    $targetClass = $em->getAssociationTargetClass($entityName, $associateName);
                    if (!isset($gathered[$targetClass])) {
                        $gathered[$targetClass] = [];
                    }
                    if (!is_array($associationInfo)) {
                        $found[] = $associationInfo;
                    } else {
                        $createInfo = $associationInfo['create'] ?? [];
                        $readInfo = $associationInfo['read'] ?? [];
                        $updateInfo = $associationInfo['update'] ?? [];
                        $deleteInfo = $associationInfo['delete'] ?? [];
                        $readKeys = array_keys($readInfo);
                        $updateKeys = array_keys($updateInfo);
                        $deleteKeys = array_keys($deleteInfo);
                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $found = array_merge($found, $readKeys, $updateKeys, $deleteKeys);
                        $subParams = array_merge($createInfo, $readInfo, $updateInfo, $deleteInfo);
                        if ($subParams !== []) {
                            /** @var RepositoryContract $targetRepo */
                            $targetRepo = $em->getRepository($targetClass);
                            $targetRepo->init($repo->getArrayHelper(), $repo->getTTPath(), $repo->getTTFallBack());
                            $gathered = $targetRepo->gatherPrePopulateEntityIds($subParams, $gathered);
                        }
                    }
                    $gathered[$targetClass] = array_unique(array_merge($gathered[$targetClass], $found));
                }
            }
        }

        return $gathered;
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $params
     * @param array $options
     * @param array $optionOverrides
     * @param string $action
     * @internal param GenericEventArgsContract $args
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function prePopulateEntities(array $params, array $options, array $optionOverrides, string $action):void
    {
        $repo = $this->getRepository();

        $arrayHelper = $repo->getArrayHelper();
        $sharedArray = $arrayHelper->getArray();
        /** @noinspection NullPointerExceptionInspection */
        $actionSettings = $repo->getConfigArrayHelper()->getArray()[$action] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $prePopulateEntities = $arrayHelper->findSetting([$actionSettings, $options, $optionOverrides], 'prePopulateEntities') ?? true;
        // If pre pop is true, if this isn't a create we get the top level ids to add to the gathered list. Then we gather the rest of the ids
        if ($prePopulateEntities === true) {

            if ($action !== 'create') {
                $className = $repo->getClassNameBase();
                $gathered = [
                    $className => array_keys($params),
                ];
            } else {
                $gathered = [];
            }

            $gathered = $this->gatherPrePopulateEntityIds($params, $gathered);

            $this->convertGatheredToPrePopulation($gathered, $sharedArray);

        }
    }

    /**
     * @param array $gathered
     * @param \ArrayObject $sharedArray
     */
    protected function convertGatheredToPrePopulation (array $gathered, \ArrayObject $sharedArray):void
    {
        $em = $this->getRepository()->getEm();
        if (!isset($sharedArray[static::PRE_POPULATED_ENTITIES_KEY])) {
            $sharedArray[static::PRE_POPULATED_ENTITIES_KEY] = [];
        }
        $prePopulate = [];

        foreach ($gathered as $key => $ids) {
            if ($ids !== []) {
                $prePopulate[$key] = [];
                $targetRepo = $em->getRepository($key);
                $foundEntities = $targetRepo->findIn('id', $ids);
                /** @var EntityContract $foundEntity */
                foreach ($foundEntities as $foundEntity) {
                    $foundEntity->setPrePopulated(true);
                    $prePopulate[$key][$foundEntity->getId()] = $foundEntity;
                }

                if (!isset($sharedArray[static::PRE_POPULATED_ENTITIES_KEY][$key])) {
                    $sharedArray[static::PRE_POPULATED_ENTITIES_KEY][$key] = [];
                }

                /** @noinspection SlowArrayOperationsInLoopInspection */
                $sharedArray[static::PRE_POPULATED_ENTITIES_KEY][$key] = array_replace($sharedArray[static::PRE_POPULATED_ENTITIES_KEY][$key], $prePopulate[$key]);
            }
        }
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
            $action = $eventArgs->getArgs()['action'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            $params = $this->convertSimpleParams($params, $frontEndOptions);
            $this->prePopulateEntities($params, $options, $optionOverrides, $action);
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
            $action = $eventArgs->getArgs()['action'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            $params = $this->convertSimpleParams($params, $frontEndOptions);
            $this->prePopulateEntities($params, $options, $optionOverrides, $action);
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
            $options = $eventArgs->getArgs()['options'];
            $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
            $action = $eventArgs->getArgs()['action'];
            $this->checkBatchMax($params, $options, $optionOverrides);
            $params = $this->convertSimpleParams($params, $frontEndOptions);
            $this->prePopulateEntities($params, $options, $optionOverrides, $action);
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
                $entity->setConfigArrayHelper($eventArgs->getArgs()['sharedConfig']);
                $entity->setTTPath($repo->getTTPath());
                $entity->setTTFallBack($repo->getTTFallBack());
                $entity->setLastMode($eventArgs->getArgs()['lastMode']);
                $entity->setArrayHelper($repo->getArrayHelper());
            }
        }
        $entity->init($eventArgs->getArgs()['action'] , $repo->getArrayHelper(), $repo->getTTPath(), $repo->getTTFallBack());
        if ($entitiesShareConfigs === true) {
            $eventArgs->getArgs()['sharedConfig'] = $entity->getConfigArrayHelper();
            $eventArgs->getArgs()['lastMode'] = $entity->getLastMode();
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
                throw DataBindHelperException::moreRowsRequestedThanBatchMax($count, $maxBatch);
            }
        }
    }

    /**
     * @param array $params
     * @param array $frontEndOptions
     * @return array
     */
    protected function convertSimpleParams(array $params, array $frontEndOptions):array
    {
        $convert = $frontEndOptions['simplifiedParams']??false;
        if ($convert === true) {
            return $this->doConvertSimpleParams($params, true);
        }

        return $params;
    }

    /**
     * @param array $params
     * @param bool $topLevel
     * @param string $defaultAssignType
     * @return array
     */
    protected function doConvertSimpleParams (array $params, bool $topLevel = false, string $defaultAssignType=null):array
    {
        if ($topLevel !== true) {
            $converted = [];
            foreach ($params as $param) {
                if ($defaultAssignType !== null && isset($param['assignType']) === false) {
                    $param['assignType'] = $defaultAssignType;
                }
                $keys = array_keys($param);
                $keys = array_diff($keys, ['assignType', 'chainType']);
                $keysCount = count($keys);
                if (
                    // No id means it is a create
                    (
                        isset($param['id']) === false
                        &&
                        isset($param['chainType']) === false
                    )
                    ||
                    (isset($param['chainType']) === true && $param['chainType'] === 'create')

                ) {
                    unset($param['chainType']);
                    if (isset($converted['create']) === false) {
                        $converted['create'] = [];
                    }
                    $converted['create'][] = $this->doConvertSimpleParamsChain($param);
                } else if (
                    //Just an id means it's a read
                    (
                        isset($param['chainType']) === false
                        &&
                        isset($param['id']) === true
                        &&
                        $keysCount === 1
                    )
                    ||
                    (isset($param['chainType']) === true && $param['chainType'] === 'read')
                ) {
                    $id = $param['id'];
                    unset($param['id'], $param['chainType']);
                    if (isset($converted['read']) === false) {
                        $converted['read'] = [];
                    }
                    $converted['read'][$id] = $this->doConvertSimpleParamsChain($param);
                } else if (
                    // An id and other params means it's an update
                    (
                        isset($param['chainType']) === false
                        &&
                        isset($param['id']) === true
                        &&
                        $keysCount > 1
                    )
                    ||
                    (isset($param['chainType']) === true && $param['chainType'] === 'update')

                ) {
                    $id = $param['id'];
                    unset($param['id'], $param['chainType']);
                    if (isset($converted['update']) === false) {
                        $converted['update'] = [];
                    }
                    $converted['update'][$id] = $this->doConvertSimpleParamsChain($param);
                } else if (
                    // Delete must be implicitly set
                    isset($param['chainType']) === true && $param['chainType'] === 'delete'
                ) {
                    $id = $param['id'];
                    unset($param['id'], $param['chainType']);
                    if (isset($converted['delete']) === false) {
                        $converted['delete'] = [];
                    }
                    $converted['delete'][$id] = $this->doConvertSimpleParamsChain($param);
                }
            }
        } else {
            // At the top level we just need to take out the ids and put them as keys on the array
            $converted = [];
            foreach ($params as $param) {
                if (isset($param['id']) === true) {
                    $id = $param['id'];
                    unset($param['id']);
                    $converted[$id] = $this->doConvertSimpleParamsChain($param);
                } else {
                    $converted[] = $this->doConvertSimpleParamsChain($param);
                }

            }

        }
        return $converted;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function doConvertSimpleParamsChain(array $params):array
    {
        $defaultAssignType = null;
        $arrayHelper = $this->getRepository()->getArrayHelper();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                if (isset($value['assignType']) === false) {
                    if ($arrayHelper->isNumeric($value) === true) {
                        $defaultAssignType = 'addSingle';
                    } else {
                        $value['assignType'] = 'set';
                    }
                }
                $value = $arrayHelper->wrapArray($value);
                $params[$key] = $this->doConvertSimpleParams($value, false, $defaultAssignType);
            }
        }
        return $params;
    }

}
?>