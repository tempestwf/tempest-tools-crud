<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use \Exception;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEventsConstants;
use TempestTools\Crud\Contracts\EntityContract;
use TempestTools\Crud\Contracts\QueryBuilderHelperContract;
use TempestTools\Crud\Contracts\DataBindHelperContract;
use TempestTools\Crud\Contracts\QueryBuilderWrapperContract;
use TempestTools\Crud\Contracts\RepositoryContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Wrapper\EntityManagerWrapper;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderSqlWrapper;
use TempestTools\Crud\Orm\Helper\DataBindHelper;
use TempestTools\Crud\Orm\Helper\QueryBuilderHelper;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderDqlWrapper;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, RepositoryContract
{

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    /**
     * ERRORS
     */
    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.',
        ],
        'entityToBindNotFound'=>[
            'message'=>'Error: Entity to bind not found.',
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows. count = %s, max = %s',
        ],
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.',
        ],
        'moreQueryParamsThanMax'=>[
            'message'=>'Error: More query params than passed than permitted. count = %s, max = %s'
        ],
        'queryTypeNotRecognized'=>[
            'message'=>'Error: Query type from configuration not recognized. query type = %s'
        ],
    ];

    /**
     * ENTITY_NAME_REGEX
     */
    const ENTITY_NAME_REGEX = '/\w+$/';


    /** @var  DataBindHelperContract|null  */
    protected $dataBindHelper;

    /**
     * @var array|NULL $options;
     */
    protected $options = [
        'paginate'=>true,
        'hydrate'=>true,
        'hydrationType'=>Query::HYDRATE_ARRAY,
        'transaction'=>true,
        'entitiesShareConfigs'=>true,
        'flush'=>true,
    ];


    /**
     * Makes sure the repo is ready to run
     *
     * @param $eventArgs
     * @internal param array $optionOverrides
     * @throws \RuntimeException
     */
    protected function start(GenericEventArgs $eventArgs):void
    {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_START, $eventArgs);
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        $transaction = $this->findSetting($optionOverrides, 'transaction');

        if ($transaction !== false) {
            $this->getEntityManager()->getConnection()->beginTransaction();
        }
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true):void
    {
        $startPath = $this->getTTPath();
        $startFallback = $this->getTTFallBack();
        if ($force === true || $this->getEvm() === null) {
            $this->setEvm(new EventManager());
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->addEventSubscriber($this);
        }

        if ($force === true || $this->getDataBindHelper() === null) {
            $this->setDataBindHelper(new DataBindHelper(new EntityManagerWrapper($this->getEntityManager())));
        }

        if ($arrayHelper !== null && ($force === true || $this->getArrayHelper() === null)) {
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

        if ($force !== true || $this->getConfigArrayHelper() === null || $startPath !== $this->getTTPath() || $startFallback !== $this->getTTFallBack()) {
            $queryArrayHelper = new QueryBuilderHelper();
            $queryArrayHelper->setArrayHelper($this->getArrayHelper());
            $this->parseTTConfig($queryArrayHelper);
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->getDataBindHelper()->init($arrayHelper, $path, $fallBack, $force);
    }

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return array
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read (array $params=[], array $frontEndOptions=[], array $optionOverrides = []):array
    {
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'read';
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_READ, $eventArgs);
        /** @var array $params */
        $params = $eventArgs->getArgs()['params'];
        $this->checkQueryMaxParams($params, $optionOverrides);
        $qbWrapper = $this->createQueryWrapper($this->getEntityAlias());
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs->getArgs()['results'] = $this->getConfigArrayHelper()->read($qbWrapper, $params, $frontEndOptions, $this->getOptions(), $optionOverrides);

        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_READ, $eventArgs);

        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \RuntimeException
     */
    public function createQueryWrapper(string $entityAlias):QueryBuilderWrapperContract
    {
        /** @noinspection NullPointerExceptionInspection */
        $queryType = $this->getConfigArrayHelper()->getArray()['settings']['queryType'] ?? 'dql';
        if ($queryType === 'dql') {
            $qb = $this->createQueryBuilder($entityAlias);
            $qbWrapper = new QueryBuilderDqlWrapper($qb);
        } else if ($queryType === 'sql') {
            $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
            $qbWrapper = new QueryBuilderSqlWrapper($qb);
        } else {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('queryTypeNotRecognized')['message'], $queryType));
        }

        return $qbWrapper;
    }

    /**
     * @return string
     */
    protected function getEntityAlias():string
    {
        $entityName = $this->getEntityName();
        preg_match(static::ENTITY_NAME_REGEX, $entityName, $matches);
        return strtolower($matches[0][0]);
    }

    /**
     * @param array $values
     * @param array $optionOverrides
     * @throws \RuntimeException
     */
    protected function checkQueryMaxParams(array $values, array $optionOverrides):void
    {
        $maxBatch = $this->findSetting($optionOverrides, 'queryMaxParams');
        if ($maxBatch !== NULL) {
            $count = count($values, COUNT_RECURSIVE);

            if ($count > $maxBatch) {
                throw new \RuntimeException(sprintf($this->getErrorFromConstant('moreQueryParamsThanMax')['message'], $count, $maxBatch));
            }
        }
    }

    /**
     * @param array $overrides
     * @param string $key
     * @return mixed
     * @throws \RuntimeException
     */
    protected function findSetting(array $overrides, string $key) {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getArrayHelper()->findSetting([
            $this->getOptions(),
            $overrides,
        ], $key);
    }

    /**
     * Makes sure every wraps up
     *
     * @param bool $failure
     * @param GenericEventArgs $eventArgs
     * @internal param array $optionOverrides
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \RuntimeException
     */
    protected function stop($failure = false, GenericEventArgs $eventArgs):void
    {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_STOP, $eventArgs);
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];

        $transaction = $this->findSetting($optionOverrides, 'transaction');
        $flush = $this->findSetting($optionOverrides, 'flush');

        if ($failure === false && $flush === true) {
            $this->getEntityManager()->flush();
        }

        if (
            $transaction !== false
        ) {
            if ($failure === true) {
                $this->getEntityManager()->getConnection()->rollBack();
            } else {
                $this->getEntityManager()->getConnection()->commit();
            }
        }
    }

    /**
     * Makes event args to use
     *
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return GenericEventArgs
     * @throws \RuntimeException
     */
    protected function makeEventArgs(array $params, array $optionOverrides = [], array $frontEndOptions=[]): Events\GenericEventArgs
    {
        $entitiesShareConfigs = $this->findSetting($optionOverrides, 'entitiesShareConfigs');
        return new GenericEventArgs(new \ArrayObject([
            'params'=>$params,
            'arrayHelper'=>$this->getArrayHelper(),
            'results'=>[],
            'self'=>$this,
            'optionOverrides'=>$optionOverrides,
            'entitiesShareConfigs'=>$entitiesShareConfigs,
            'frontEndOptions'=>$frontEndOptions,
        ]));
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
     * @throws Exception
     */
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'create';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_CREATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $this->checkBatchMax($params, $optionOverrides);
            foreach ($params as $batchParams) {
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doCreate($eventArgs);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_CREATE_BATCH, $eventArgs);
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function doCreate (GenericEventArgs $eventArgs):void
    {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_CREATE, $eventArgs);
        $result = $this->doCreateSingle($eventArgs);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_CREATE, $eventArgs);
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @return EntityContract
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function doCreateSingle(GenericEventArgs $eventArgs): EntityContract
    {
        $className = $this->getClassName();
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
     * @throws Exception
     */
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'update';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_UPDATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $this->checkBatchMax($params, $optionOverrides);
            /** @noinspection NullPointerExceptionInspection */
            $entities = $this->getDataBindHelper()->findEntitiesFromArrayKeys($params, $this);
            /** @var EntityAbstract $entity */
            foreach ($entities as $entity) {
                $batchParams = $entity->getBindParams();
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doUpdate($eventArgs, $entity);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_UPDATE_BATCH, $eventArgs);
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function doUpdate (GenericEventArgs $eventArgs, EntityContract $entity):void
    {
        $evm = $this->getEvm();
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
     * @throws Exception
     */
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array
    {

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'delete';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEventsConstants::PRE_DELETE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $this->checkBatchMax($params, $optionOverrides);
            /** @noinspection NullPointerExceptionInspection */
            $entities = $this->getDataBindHelper()->findEntitiesFromArrayKeys($params, $this);
            /** @var EntityAbstract $entity */
            foreach ($entities as $entity) {
                $batchParams = $entity->getBindParams();
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doDelete($eventArgs, $entity);
            }
            $evm->dispatchEvent(RepositoryEventsConstants::POST_DELETE_BATCH, $eventArgs);
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param EntityContract $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function doDelete (GenericEventArgs $eventArgs, EntityContract $entity):void
    {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_DELETE, $eventArgs);
        $result = $this->processSingleEntity($eventArgs, $entity, true);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_DELETE, $eventArgs);
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param EntityContract $entity
     * @param bool $remove
     * @return EntityContract
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function processSingleEntity (GenericEventArgs $eventArgs, EntityContract $entity, bool $remove=false): EntityContract
    {
        $entitiesShareConfigs = $eventArgs->getArgs()['entitiesShareConfigs'];
        if ($entitiesShareConfigs === true) {
            if (isset($eventArgs->getArgs()['sharedConfig'])) {
                $sharedConfig = $eventArgs->getArgs()['sharedConfig'];
                $entity->setConfigArrayHelper($sharedConfig);
            }
        }
        $entity->init($eventArgs->getArgs()['action'] , $this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
        if ($entitiesShareConfigs === true) {
            $eventArgs->getArgs()['sharedConfig'] = $entity->getConfigArrayHelper();
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->getDataBindHelper()->bind($entity, $eventArgs->getArgs()['params']);
        if ($remove === true) {
            $this->getEntityManager()->remove($entity);
        } else {
            $this->getEntityManager()->persist($entity);
        }
        return $entity;
    }



    /**
     * @param array $values
     * @param array $optionOverrides
     * @throws \RuntimeException
     */
    protected function checkBatchMax(array $values, array $optionOverrides):void
    {
        $maxBatch = $this->findSetting($optionOverrides, 'batchMax');
        if ($maxBatch !== NULL) {
            $count = count($values, COUNT_RECURSIVE);

            if ($count > $maxBatch) {
                throw new \RuntimeException(sprintf($this->getErrorFromConstant('moreRowsRequestedThanBatchMax')['message'], $count, $maxBatch));
            }
        }
    }

    /**
     * @param string $fieldName
     * @param array $values
     * @return mixed
     */
    public function findIn(string $fieldName, array $values)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getClassName(), 'e')
            ->where($qb->expr()->in('e.'.$fieldName, ':in'));
        $qb->setParameter(':in', $values);
        return $qb;
    }

    /**
     * Subscribes to the available events that are present on the class
     * @return array
     */
    public function getSubscribedEvents():array
    {
        $all = RepositoryEventsConstants::getAll();
        $subscribe = [];
        foreach ($all as $event) {
            if (method_exists ($this, $event)) {
                $subscribe[] = $event;
            }
        }
        return $subscribe;
    }

    /**
     * @return array
     */
    abstract public function getTTConfig(): array;

    /**
     * @return array|NULL
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array|NULL $options
     */
    public function setOptions($options):void
    {
        $this->options = $options;
    }

    /**
     * @return null|DataBindHelperContract
     */
    public function getDataBindHelper(): ?DataBindHelperContract
    {
        return $this->dataBindHelper;
    }

    /**
     * @param DataBindHelperContract $dataBindHelper
     */
    public function setDataBindHelper(DataBindHelperContract $dataBindHelper):void
    {
        $this->dataBindHelper = $dataBindHelper;
    }



    /**
     * @return NULL|QueryBuilderHelperContract
     */
    public function getConfigArrayHelper():?QueryBuilderHelperContract
    {
        return $this->configArrayHelper;
    }

    /**
     * @param QueryBuilderHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(QueryBuilderHelperContract $configArrayHelper):void
    {
        $this->configArrayHelper = $configArrayHelper;
    }
}
?>