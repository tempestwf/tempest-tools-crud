<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Mockery\Exception;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
use TempestTools\Crud\Contracts\QueryHelper as QueryHelperContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
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
     * Makes sure the repo is ready to run
     *
     * @param $eventArgs
     * @internal param array $optionOverrides
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
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true) {
        if ($arrayHelper !== NULL && ($force === true || $this->getArrayHelper() === NULL)) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== NULL && ($force === true || $this->getTTPath() === NULL)) {
            $this->setTTPath($path);
        }

        if ($fallBack !== NULL && ($force === true || $this->getTTFallBack() === NULL)) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        if ($force !== true || $this->getConfigArrayHelper() === NULL) {
            $this->parseTTConfig();
        }

    }

    /**
     * @param array $overrides
     * @param string $key
     * @return mixed
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
            if (isset($params['batch'])) {
                $eventArgs->getArgs()['batchParams'] = $eventArgs->getArgs()['params'];
                $evm->dispatchEvent(RepositoryEvents::PRE_CREATE_BATCH, $eventArgs);
                /** @var array[] $batch */
                $batch = $params['batch'];
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
            $count = count($values) > $maxBatch;
            $maxBatchIncludesChains = $configArrayHelper->parseArrayPath(['batchMaxIncludesChains']);
            $maxBatchIncludesAssigns = $configArrayHelper->parseArrayPath(['batchMaxIncludesAssigns']);
            if ($maxBatchIncludesChains === true || $maxBatchIncludesAssigns === true) {

                $newCount = count(array_filter($values, function($element) use ($maxBatchIncludesAssigns, $maxBatchIncludesAssigns){
                    return ($maxBatchIncludesAssigns && isset($element['chainType'])) || ($maxBatchIncludesAssigns && isset($element['assignType']));
                }));

                $count += $newCount;
            }
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
        $this->bind($entity, $eventArgs->getArgs()['params']);
        $this->getEntityManager()->persist($entity);
        return $entity;
    }

    /**
     * @param EntityAbstract $entity
     * @param array $params
     * @return EntityAbstract
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Mockery\Exception
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function bind(EntityAbstract $entity, array $params): EntityAbstract
    {
        $entity->setArrayHelper($this->getArrayHelper());
        $metadata = $this->getEntityManager()->getClassMetadata($entity);
        $associateNames = $metadata->getAssociationNames();
        foreach ($params as $fieldName => $value) {
            if (in_array($fieldName, $associateNames, true)) {
                $targetClass = $metadata->getAssociationTargetClass($fieldName);
                $this->bindAssociation($entity, $fieldName, $value, $targetClass);
            } else {
                $entity->setField($fieldName, $value);
            }
        }
        return $entity;
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityAbstract $entity
     * @param string $associationName
     * @param array $params
     * @param string $targetClass
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \InvalidArgumentException
     * @throws \Mockery\Exception
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function bindAssociation(EntityAbstract $entity, string $associationName, array $params, string $targetClass)
    {
        //TODO: Consider using batches in future, the array of sub elements would need to be chunked up, and then passed to the repos on chain
        // Then it would need to be restitched with params, so we can check the assign type, and then bound to the entity. A pain in the arse.

        /** @var RepositoryAbstract $repo */
        $repo = $this->getEntityManager()->getRepository($targetClass);
        $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack(), false);
        $chainOverrides = ['transaction'=>false, 'flush'=>false];
        // TODO: Use a contract here instead
        if (!$repo instanceof RepositoryAbstract) {
            throw new \RuntimeException($this->getErrorFromConstant('wrongTypeOfRepo'));
        }

        $params = !isset($params[0]) ? [$params] : $params;
        foreach ($params as $values) {
            $values = $entity->processAssociationParams($associationName, $values);
            $assignType = $values['assignType'] ?? NULL;
            $chainType = $values['chainType'] ?? null;

            if (isset($values['assignType'])) {
                unset($values['assignType']);
            }

            if (isset($values['chainType'])) {
                unset($values['chainType']);
            }

            $foundEntity = null;
            /** @var EntityAbstract $foundEntity */
            if ($chainType !== null) {
                switch ($chainType) {
                    case 'create':
                        $foundEntity = $repo->create($values, $chainOverrides)[0];
                        break;
                    /*case 'update':
                        $foundEntity = $repo->update($info, $chainOverrides)[0];
                        break;
                    case 'delete':
                        $foundEntity = $repo->delete($info, $chainOverrides)[0];
                        break;*/
                }
            } else {
                $foundEntity = $repo->findOneBy($values['id']);
            }

            if ($foundEntity !== NULL) {
                if ($assignType !== NULL) {
                    $entity->bindAssociation($assignType, $associationName, $foundEntity, true);
                }
            } else {
                throw new \RuntimeException($this->getErrorFromConstant('entityToBindNotFound')['message']);
            }
        }
    }

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
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($this);
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
}
?>