<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Crud\Constants\RepositoryEvents;
use TempestTools\Crud\Contracts\QueryHelper as QueryHelperContract;
use TempestTools\Crud\Doctrine\Helper\QueryHelper;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber {

    use ArrayHelperTrait;

    /** @var EventManager|null */
    public $evm;

    /** @var  QueryHelperContract|null  */
    protected $queryHelper;

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

    public function getSubscribedEvents():array
    {
        return RepositoryEvents::getAll();
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