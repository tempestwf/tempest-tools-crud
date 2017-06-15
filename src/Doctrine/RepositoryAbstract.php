<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
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
     * Makes sure the repo is ready to run
     *
     * @throws \RuntimeException
     */
    protected function start() {
        /** @noinspection NullPointerExceptionInspection */
        if (
            !isset($this->getArrayHelper()->getArray()['backend']['options']['transaction']) ||
            $this->getArrayHelper()->getArray()['backend']['options']['transaction'] !== false
        ) {
            $this->getEntityManager()->getConnection()->beginTransaction();
        }

    }

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @throws \RuntimeException
     */
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL) {
        if ($arrayHelper !== NULL) {
            $this->setArrayHelper($arrayHelper);
        }

        if ($path !== NULL) {
            $this->setTTPath($path);
        }

        if ($fallBack !== NULL) {
            $this->setTTFallBack($fallBack);
        }

        if (!$this->getArrayHelper() instanceof ArrayHelperContract) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper'));
        }

        $this->parseTTConfig();
    }

    /**
     * Makes sure every wraps up
     *
     * @param bool $failure
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function stop($failure = false) {
        /** @noinspection NullPointerExceptionInspection */
        if (
            !isset($this->getArrayHelper()->getArray()['backend']['options']['transaction']) ||
            $this->getArrayHelper()->getArray()['backend']['options']['transaction'] !== false
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
     * @param array $params
     * @return GenericEventArgs
     */
    protected function makeEventArgs(array $params): Events\GenericEventArgs
    {
        return new GenericEventArgs(new \ArrayObject(['params'=>$params,'arrayHelper'=>$this->getArrayHelper(), 'results'=>[], 'self'=>$this]));
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Mockery\Exception
     */
    public function create(array $params){
        $this->start();
        $evm = $this->getEvm();
        $eventArgs = $this->makeEventArgs($params);
        $eventArgs->getArgs()['action'] = 'create';

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
                $this->doCreate($eventArgs);
            }
        } catch (Exception $e) {
            $this->stop(true);
            throw $e;
        }
        $this->stop();
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
            if ($maxBatchIncludesChains === true) {

                $newCount = count(array_filter($values, function($element) {
                    return isset($element['chainType']);
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
        $entity->init($eventArgs->getArgs()['action'] , $this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
        $this->bind($entity, $eventArgs->getArgs()['params']);
        return $entity;
    }

    /**
     * @param EntityAbstract $entity
     * @param array $params
     * @return EntityAbstract
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
                if (isset($value[0]) && is_array($value[0])) {
                    /** @var array $value */
                    foreach ($value as $info) {
                        $this->bindAssociation($entity, $fieldName, $info, $targetClass);
                    }
                } else {
                    $this->bindAssociation($entity, $fieldName, $value, $targetClass);
                }
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
     */
    public function bindAssociation(EntityAbstract $entity, string $associationName, array $params, string $targetClass) {
        $params = $entity->processAssociationParams($associationName, $params);
        $assignType = $params['assignType'] ?? 'set';
        $chainType = $params['chainType'] ?? NULL;

        if (isset($params['assignType'])) {
            unset($params['assignType']);
        }

        if (isset($params['chainType'])) {
            unset($params['chainType']);
        }
        /** @var RepositoryAbstract $repo */
        $repo = $this->getEntityManager()->getRepository($targetClass);
        $repo->setArrayHelper($this->getArrayHelper());

        $foundEntity = NULL;
        if ($chainType !== NULL) {
            // TODO: Use a contract here instead
            if (!$repo instanceof RepositoryAbstract) {
                throw new \RuntimeException($this->getErrorFromConstant('wrongTypeOfRepo'));
            }

            switch ($chainType) {
                case 'create':
                    $foundEntity = $repo->create($params)[0];
                    break;
                /*case 'update':
                    $foundEntity = $repo->update($info)[0];
                    break;
                case 'delete':
                    $foundEntity = $repo->delete($info)[0];
                    break;*/
            }
        } else {
            $foundEntity = $repo->findOneBy($params['id']);
        }

        if ($foundEntity === NULL) {
            $entity->bindAssociation($assignType, $associationName, $foundEntity, true);
        } else {
            throw new \RuntimeException($this->getErrorFromConstant('entityToBindNotFound')['message']);
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
}
?>