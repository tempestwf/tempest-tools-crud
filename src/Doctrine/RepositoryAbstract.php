<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
use TempestTools\Crud\Contracts\QueryHelper as QueryHelperContract;
use TempestTools\Crud\Doctrine\Helper\QueryHelper;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber {

    use ArrayHelperTrait, ErrorConstantsTrait;

    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.'
        ]
    ];

    /** @var EventManager|null */
    public $evm;

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
        /** @noinspection NullPointerExceptionInspection */
        if (
            !isset($this->getArrayHelper()->getArray()['backend']['options']['transaction']) ||
            $this->getArrayHelper()->getArray()['backend']['options']['transaction'] !== false
        ) {
            $this->getEntityManager()->getConnection()->beginTransaction();
        }
    }

    /**
     * @param array $params
     * @throws \RuntimeException
     */
    public function create(array $params){
        $this->start();
        $evm = $this->getEvm();
        $evm->dispatchEvent(RepositoryEvents::PRE_CREATE);
        $evm->dispatchEvent(RepositoryEvents::PRE_CREATE);


    }

    public function __construct($em, ClassMetadata $class){
        parent::__construct($em, $class);
        $this->setEvm(new EventManager());
        $this->setQueryHelper(new QueryHelper());
    }

    /**
     * @param EventManager|null $evm
     * @return RepositoryAbstract
     */
    public function setEvm(EventManager $evm):RepositoryAbstract
    {
        $this->evm = $evm;
        return $this;
    }

    /**
     * @return EventManager|null
     */
    public function getEvm()
    {
        return $this->evm;
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
}
?>