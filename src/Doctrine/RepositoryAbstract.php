<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\Exception;
use TempestTools\Common\Contracts\ArrayHelpable;
use TempestTools\Common\Contracts\Evm;
use TempestTools\Common\Contracts\TTConfig;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
use TempestTools\Crud\Contracts\QueryHelper as QueryHelperContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Helper\QueryHelper;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, TTConfig, Evm, ArrayHelpable {

    use ArrayHelperTrait, ErrorConstantsTrait, TTConfigTrait, EvmTrait;

    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.',
        ],
    ];

    /** @var  QueryHelperContract|null  */
    protected $queryHelper;

    /**
     * Makes sure the repo is ready to run
     *
     * @throws \RuntimeException
     */
    protected function start() {
        if ($this->getArrayHelper() === NULL) {
            throw new \RuntimeException($this->getErrorFromConstant('noArrayHelper')['message']);
        }
        $this->parseTTConfig();
        /** @noinspection NullPointerExceptionInspection */
        if (
            !isset($this->getArrayHelper()->getArray()['backend']['options']['transaction']) ||
            $this->getArrayHelper()->getArray()['backend']['options']['transaction'] !== false
        ) {
            $this->getEntityManager()->getConnection()->beginTransaction();
        }
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($this);
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
        return new GenericEventArgs(new \ArrayObject(['params'=>$params,'arrayHelper'=>$this->getArrayHelper(), 'results'=>[]]));
    }

    /**
     * @param array $params
     * @return mixed
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
     * @param GenericEventArgs $eventArgs
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

    protected function doCreateSingle(GenericEventArgs $eventArgs) {
        $className = $this->getClassName();
        $entity = new $className($this->getArrayHelper());
        $this->bind($entity,  $eventArgs);
        return $entity;
    }

    /**
     * @param EntityAbstract $entity
     * @param GenericEventArgs $eventArgs
     * @return EntityAbstract
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function bind(EntityAbstract $entity, GenericEventArgs $eventArgs): EntityAbstract
    {
        $entity->setArrayHelper($this->getArrayHelper());
        /** @var array $params */
        $params = $eventArgs->getArgs()['params'];
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
                    $this->bindAssociation($eventArgs->getArgs()['action'], $entity, $fieldName, $value, $targetClass);
                }
            } else {
                $entity->setField($eventArgs->getArgs()['action'], $fieldName, $value);
            }
        }
        return $entity;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    public function bindAssociation($action, EntityAbstract $entity, string $associationName, array $info, string $targetClass) {
        $bindType = $info['bindType'] ?? 'set';
        $chainType = $info['chainType'] ?? NULL;
        /** @var RepositoryAbstract $repo */
        $repo = $this->getEntityManager()->getRepository($targetClass);
        $repo->setArrayHelper($this->getArrayHelper());

        // TODO: Add a pre chain event
        if ($chainType !== NULL && $entity->canChain($action, $associationName, $chainType)) {
            //TODO: Throw error if wrong type of repo
            //TODO: Use constants instead of strings
            switch ($chainType) {
                case 'create':
                    $foundEntity = $repo->create($info)[0];
                    break;
                case 'update':
                    $foundEntity = $repo->update($info)[0];
                    break;
                case 'delete':
                    $foundEntity = $repo->delete($info)[0];
                    break;
            }
        } else {
            $foundEntity = $repo->findOneBy($info['id']);
        }
        // TODO: Add a pre bind event
        $entity->bindAssociation($action, $bindType, $foundEntity);
    }

    public function __construct($em, ClassMetadata $class){
        parent::__construct($em, $class);
        $this->setEvm(new EventManager());
        $this->setQueryHelper(new QueryHelper());
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