<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use \Exception;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
use TempestTools\Crud\Contracts\Entity;
use TempestTools\Crud\Contracts\QueryHelper as QueryHelperContract;
use TempestTools\Crud\Contracts\DataBindHelper as DataBindHelperContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Helper\DataBindHelper;
use TempestTools\Crud\Doctrine\Helper\QueryHelper;
use TempestTools\Common\Contracts\ArrayHelper as ArrayHelperContract;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.',
        ],
        'entityToBindNotFound'=>[
            'message'=>'Error: Entity to bind not found.',
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows. count = %2, max = %s',
        ],
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.',
        ],
        'moreQueryParamsThanMax'=>[
            'message'=>'Error: More query params than passed than permitted. count = %2, max = %s'
        ]
    ];

    /** @var  QueryHelperContract|null  */
    protected $queryHelper;

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
    protected function start(GenericEventArgs $eventArgs) {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_START, $eventArgs);
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
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true)
    {
        if ($force === true || $this->getEvm() === null) {
            $this->setEvm(new EventManager());
            /** @noinspection NullPointerExceptionInspection */
            $this->getEvm()->addEventSubscriber($this);
        }


        if ($force === true || $this->getQueryHelper() === null) {
            $this->setQueryHelper(new QueryHelper());
        }
        if ($force === true || $this->getDataBindHelper() === null) {
            $this->setDataBindHelper(new DataBindHelper($this->getEntityManager()));
        }

        if ($arrayHelper !== null && ($force === true || $this->getArrayHelper() === null)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== null && ($force === true || $this->getTTPath() === null)) {
            $this->setTTPath($path);
        }

        if ($fallBack !== null && ($force === true || $this->getTTFallBack() === null)) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        if ($force !== true || $this->getConfigArrayHelper() === null) {
            $entityArrayHelper = new QueryHelper();
            $entityArrayHelper->setArrayHelper($this->getArrayHelper());
            $this->parseTTConfig($entityArrayHelper);
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->getDataBindHelper()->init($arrayHelper, $path, $fallBack, $force);
    }

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read (array $params, array $optionOverrides = [], array $frontEndOptions=[]) {
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'read';
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VALIDATE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VERIFY_READ, $eventArgs);
        /** @var array $params */
        $params = $eventArgs->getArgs()['params'];
        $this->checkQueryMaxParams($params, $optionOverrides);
        $qb = $this->createQueryBuilder(get_class($this)[0]);
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs->getArgs()['results'] = $this->getConfigArrayHelper()->read($qb, $params, $this->getOptions(), $optionOverrides, $frontEndOptions);

        $evm->dispatchEvent(RepositoryEvents::PROCESS_RESULTS_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::POST_READ, $eventArgs);

        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param array $values
     * @param array $optionOverrides
     * @throws \RuntimeException
     */
    protected function checkQueryMaxParams(array $values, array $optionOverrides) {
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
    protected function stop($failure = false, GenericEventArgs $eventArgs) {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_STOP, $eventArgs);
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
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions=[]){

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'create';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEvents::PRE_CREATE_BATCH, $eventArgs);
            /** @var array $params */
            $params = $eventArgs->getArgs()['params'];
            $this->checkBatchMax($params, $optionOverrides);
            foreach ($params as $batchParams) {
                $eventArgs->getArgs()['params'] = $batchParams;
                /** @noinspection DisconnectedForeachInstructionInspection */
                $this->doCreate($eventArgs);
            }
            $evm->dispatchEvent(RepositoryEvents::POST_CREATE_BATCH, $eventArgs);
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
    protected function doCreate (GenericEventArgs $eventArgs) {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VALIDATE_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VERIFY_CREATE, $eventArgs);
        $result = $this->doCreateSingle($eventArgs);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEvents::PROCESS_RESULTS_CREATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::POST_CREATE, $eventArgs);
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @return Entity
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function doCreateSingle(GenericEventArgs $eventArgs): Entity
    {
        $className = $this->getClassName();
        /** @var EntityAbstract $entity */
        return $this->processSingleEntity($eventArgs, new $className());
    }


    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions=[]){

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'update';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEvents::PRE_UPDATE_BATCH, $eventArgs);
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
            $evm->dispatchEvent(RepositoryEvents::POST_UPDATE_BATCH, $eventArgs);
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param Entity $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function doUpdate (GenericEventArgs $eventArgs, Entity $entity) {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VALIDATE_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VERIFY_UPDATE, $eventArgs);
        $result = $this->processSingleEntity($eventArgs, $entity);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEvents::PROCESS_RESULTS_UPDATE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::POST_UPDATE, $eventArgs);
    }

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions=[]){

        /** @noinspection NullPointerExceptionInspection */
        $this->getArrayHelper()->wrapArray($params);
        $eventArgs = $this->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'delete';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
            $evm->dispatchEvent(RepositoryEvents::PRE_DELETE_BATCH, $eventArgs);
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
            $evm->dispatchEvent(RepositoryEvents::POST_DELETE_BATCH, $eventArgs);
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param Entity $entity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function doDelete (GenericEventArgs $eventArgs, Entity $entity) {
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VALIDATE_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::VERIFY_DELETE, $eventArgs);
        $result = $this->processSingleEntity($eventArgs, $entity, true);
        $eventArgs->getArgs()['results'][] = $result;
        $evm->dispatchEvent(RepositoryEvents::PROCESS_RESULTS_DELETE, $eventArgs);
        $evm->dispatchEvent(RepositoryEvents::POST_DELETE, $eventArgs);
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @param Entity $entity
     * @param bool $remove
     * @return Entity
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function processSingleEntity (GenericEventArgs $eventArgs, Entity $entity, bool $remove=false): Entity
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
    protected function checkBatchMax(array $values, array $optionOverrides) {
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
     * @return QueryBuilder
     */
    public function findIn(string $fieldName, array $values): QueryBuilder
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
        $all = RepositoryEvents::getAll();
        $subscribe = [];
        foreach ($all as $event) {
            if (method_exists ($this, $event)) {
                $subscribe[] = $event;
            }
        }
        return $subscribe;
    }

    /**
     * @param null|QueryHelperContract $queryHelper
     * @return RepositoryAbstract
     */
    public function setQueryHelper(QueryHelperContract $queryHelper):RepositoryAbstract
    {
        $this->queryHelper = $queryHelper;
        return $this;
    }

    /**
     * @return null|QueryHelperContract
     */
    public function getQueryHelper():?QueryHelperContract
    {
        return $this->queryHelper;
    }

    public function getTTConfig(): array
    {
        return [];
    }

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
    public function setOptions($options)
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
    public function setDataBindHelper(DataBindHelperContract $dataBindHelper)
    {
        $this->dataBindHelper = $dataBindHelper;
    }



    /**
     * @return NULL|QueryHelperContract
     */
    public function getConfigArrayHelper():?QueryHelperContract
    {
        return $this->configArrayHelper;
    }

    /**
     * @param QueryHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(QueryHelperContract $configArrayHelper)
    {
        $this->configArrayHelper = $configArrayHelper;
    }
}
?>