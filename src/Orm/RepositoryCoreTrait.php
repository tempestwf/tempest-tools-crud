<?php
namespace TempestTools\Crud\Orm;

use \Exception;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Constants\RepositoryEventsConstants;
use TempestTools\Crud\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Contracts\Orm\Helper\QueryBuilderHelperContract;
use TempestTools\Crud\Contracts\Orm\Helper\DataBindHelperContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Crud\Orm\Helper\DataBindHelper;
use TempestTools\Crud\Orm\Helper\QueryBuilderHelper;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Orm\Utility\EventManagerWrapperTrait;

/**
 * A trait that provides the repository related functionality to a class
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
trait RepositoryCoreTrait
{
    use TTConfigTrait, EventManagerWrapperTrait;

    /** @var  DataBindHelperContract|null  */
    protected $dataBindHelper;


    /** @var  EntityManagerWrapperContract $em */
    protected $em;
    /**
     * @var array|NULL $options;
     */
    protected $options;

    /** @var array  */
    protected /** @noinspection PropertyCanBeStaticInspection */ $availableModes = ['create', 'read', 'update', 'delete'];


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Initializes the classes with the helpers and config context path information
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force = false):void
    {
        $force = $this->coreInit($arrayHelper, $path, $fallBack, $force);
        $this->queryBuilderInit($force);
        $this->eventManagerInit($force);
        $this->entityManagerInit($force);
        $this->dataBindHelperInit($force);

    }

    /**
     * Initializes the entity manager wrapper
     * @param bool $force
     */
    protected function entityManagerInit (bool $force= true):void
    {
        if ($force === true || $this->getEm() === null) {
            $this->setEm($this->createEntityManagerWrapper());
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection
     * Initializes the data bind helper
     * @param bool $force
     * @throws \RuntimeException
     */
    protected function dataBindHelperInit(bool $force):void
    {
        if ($force === true || $this->getDataBindHelper() === null) {
            /** @noinspection PhpParamsInspection */
            $this->setDataBindHelper(new DataBindHelper($this));
        }
    }

    /**
     * Find entities based on array keys
     * @param array $array
     * @return array
     */
    public function findEntitiesFromArrayKeys (array $array):array
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getDataBindHelper()->findEntitiesFromArrayKeys($array);
    }


    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Initializes the query builder helper
     * @param bool $force
     * @throws \RuntimeException
     */
    protected function queryBuilderInit(bool $force= true):void
    {
        if ($force === true || $this->getConfigArrayHelper() === null ) {
            $queryArrayHelper = new QueryBuilderHelper(null, $this);
            /** @noinspection PhpParamsInspection */
            $this->parseTTConfig($queryArrayHelper);
        }
    }


    /**
     * Gets the alias for an entity to be used by default in the query
     * @return string
     */
    public function getEntityAlias():string
    {
        $entityName = $this->getEntityNameBase();
        preg_match(static::getEntityNameRegex(), $entityName, $matches);
        return strtolower($matches[0][0]);
    }

    /**
     * @return string
     */
    abstract public function getEntityNameBase(): string;

    /**
     * Regex used to make sure an entity name is appropriate
     */
    protected static function getEntityNameRegex():string {
        return '/\w+$/';
    }

    /**
     * @return EntityManagerWrapperContract
     */
    public function getEm(): ?EntityManagerWrapperContract
    {
        return $this->em;
    }

    /**
     * @param EntityManagerWrapperContract $em
     */
    public function setEm(EntityManagerWrapperContract $em):void
    {
        $this->em = $em;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * Makes event args to use
     *
     * @param array $params
     * @param array $options
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return GenericEventArgsContract
     * @throws \RuntimeException
     */
    abstract public function makeEventArgs(array $params, array $options = [], array $optionOverrides = [], array $frontEndOptions=[]): GenericEventArgsContract;
    /**
     * @return string
     * @throws \RuntimeException
     */
    abstract public function getClassNameBase():string;
    /**
     * Handles a create operation
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
        return $this->getDataBindHelper()->create($params, $optionOverrides, $frontEndOptions);
    }

    /**
     * Handles an update operation
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
        return $this->getDataBindHelper()->update($params, $optionOverrides, $frontEndOptions);
    }


    /**
     * Handles an delete operation
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
        return $this->getDataBindHelper()->delete($params, $optionOverrides, $frontEndOptions);
    }

    /**
     * Handles a read operation
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
        return $this->getConfigArrayHelper()->read($params, $frontEndOptions, $optionOverrides);
    }



    /**
     * Creates a query builder wrapper
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \RuntimeException
     */
    abstract public function createQueryWrapper(string $entityAlias):QueryBuilderWrapperContract;

    /**
     * Convenience method for find in functionality
     * @param string $fieldName
     * @param array $values
     * @return mixed
     * @throws \RuntimeException
     */
    abstract public function findIn(string $fieldName, array $values):array;

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


    abstract public function createEntityManagerWrapper():EntityManagerWrapperContract;

    /**
     * Locates all the ids and class names of entities that could be pre-populated for use with the current request.
     * @param array $params
     * @param array $gathered
     * @return array
     * @throws \InvalidArgumentException
     */
    public function gatherPrePopulateEntityIds (array $params, array $gathered=[]):array
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->getDataBindHelper()->gatherPrePopulateEntityIds($params, $gathered);
    }

    /**
     * Clears the pre-populated entities for the shared array object at the end of a transaction.
     * @internal param array $params
     * @internal param array $gathered
     */
    public function clearPrePopulatedEntities ():void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getDataBindHelper()->clearPrePopulatedEntities();
    }

    /**
     * @return array
     */
    public function getAvailableModes(): array
    {
        return $this->availableModes;
    }

}

?>