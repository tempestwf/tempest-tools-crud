<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\Exception;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
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
            'message'=>'Error: Entity to bind not found.'
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows.'
        ],
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.'
        ]
    ];

    /** @var  QueryHelperContract|null  */
    protected $queryHelper;

    /** @var  DataBindHelperContract|null  */
    protected $dataBindHelper;

    /**
     * @var array|NULL $defaultOptions;
     */
    protected $defaultOptions = [
        'paginate'=>true,
        'hydrate'=>true,
        'hydrationType'=>Query::HYDRATE_ARRAY,
        'transaction'=>true,
        'entitiesShareConfigs'=>true,
        'flush'=>true
    ];


    /**
     * RepositoryAbstract constructor.
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct($em, ClassMetadata $class){
        parent::__construct($em, $class);
        $this->setEvm(new EventManager());
        $this->setQueryHelper(new QueryHelper());
        $this->setDataBindHelper(new DataBindHelper($this->getEntityManager()));
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($this);
    }


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
            $this->parseTTConfig();
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->getDataBindHelper()->init($arrayHelper, $path, $fallBack, $force);
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
            $this->getDefaultOptions(),
            $this->getArrayHelper()->getArray()['backend']['options'],
            $overrides
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
     * @return GenericEventArgs
     * @throws \RuntimeException
     */
    protected function makeEventArgs(array $params, array $optionOverrides = []): Events\GenericEventArgs
    {
        $entitiesShareConfigs = $this->findSetting($optionOverrides, 'entitiesShareConfigs');
        return new GenericEventArgs(new \ArrayObject(['params'=>$params,'arrayHelper'=>$this->getArrayHelper(), 'results'=>[], 'self'=>$this, 'optionOverrides'=>$optionOverrides, 'entitiesShareConfigs'=>$entitiesShareConfigs]));
    }

    /**
     * @param array $params
     * @param array $optionOverrides
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Mockery\Exception
     */
    public function create(array $params, array $optionOverrides = []){
        $eventArgs = $this->makeEventArgs($params, $optionOverrides);
        $eventArgs->getArgs()['action'] = 'create';
        $evm = $this->getEvm();

        $this->start($eventArgs);

        try {
            if (isset($params['create'])) {
                $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
                $evm->dispatchEvent(RepositoryEvents::PRE_CREATE_BATCH, $eventArgs);
                /** @var array[] $batch */
                $batch = $params['create'];
                $this->checkBatchMax($batch);
                foreach ($batch as $batchParams) {
                    $eventArgs->getArgs()['params'] = $batchParams;
                    /** @noinspection DisconnectedForeachInstructionInspection */
                    $this->doCreate($eventArgs);
                }
                $evm->dispatchEvent(RepositoryEvents::POST_CREATE_BATCH, $eventArgs);
            } else {
                $this->checkBatchMax($eventArgs->getArgs()['params']);
                $this->doCreate($eventArgs);
            }
        } catch (Exception $e) {
            $this->stop(true, $eventArgs);
            throw $e;
        }
        $this->stop(false, $eventArgs);
        return $eventArgs->getArgs()['results'];
    }

    /**
     * @param array $values
     * @throws \RuntimeException
     */
    protected function checkBatchMax(array $values) {
        $configArrayHelper = $this->getConfigArrayHelper();
        $maxBatch = $configArrayHelper->parseArrayPath(['batchMax']);
        if ($maxBatch !== NULL) {
            $count = count($values, COUNT_RECURSIVE);

            if ($count > $maxBatch) {
                throw new \RuntimeException($this->getErrorFromConstant('moreRowsRequestedThanBatchMax'));
            }
        }
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Mockery\Exception
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
        $evm->dispatchEvent(RepositoryEvents::POST_COMMIT_CREATE, $eventArgs);
    }

    /**
     * @param GenericEventArgs $eventArgs
     * @return EntityAbstract
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Mockery\Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function doCreateSingle(GenericEventArgs $eventArgs): EntityAbstract
    {
        $className = $this->getClassName();
        /** @var EntityAbstract $entity */
        $entity = new $className();
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
        $this->getEntityManager()->persist($entity);
        return $entity;
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
            ->where($qb->expr()->in($fieldName, ':in'));
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
    public function getQueryHelper()
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
    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    /**
     * @param array|NULL $defaultOptions
     */
    public function setDefaultOptions($defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
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
}
?>