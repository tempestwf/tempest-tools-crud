<?php
namespace TempestTools\Scribe\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use TempestTools\Scribe\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;
use TempestTools\Scribe\Doctrine\Events\GenericEventArgs;
use TempestTools\Scribe\Doctrine\Utility\CreateEventManagerWrapperTrait;
use TempestTools\Scribe\Doctrine\Wrapper\EntityManagerWrapper;
use TempestTools\Scribe\Doctrine\Wrapper\QueryBuilderDqlWrapper;
use TempestTools\Scribe\Doctrine\Wrapper\QueryBuilderSqlWrapper;
use TempestTools\Scribe\Exceptions\Orm\RepositoryException;
use TempestTools\Scribe\Orm\RepositoryCoreTrait;

/**
 * An abstract class for Repositories that Doctrine Repositories must extend to use the functionality of this package.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, RepositoryContract
{
    use RepositoryCoreTrait, CreateEventManagerWrapperTrait;

    /**
     * Default options for a repository
     * @var array|NULL $options;
     */
    protected $options = [
        'paginate'=>true,
        'hydrate'=>true,
        'hydrationType'=>Query::HYDRATE_ARRAY,
        'transaction'=>true,
        'entitiesShareConfigs'=>true,
        'flush'=>true,
        'clearPrePopulatedEntitiesOnFlush'=>true
    ];

    /**
     * Creates an entity manager wrapper.
     * @return EntityManagerWrapperContract
     */
    public function createEntityManagerWrapper():EntityManagerWrapperContract
    {
        return new EntityManagerWrapper($this->getEntityManager());
    }



    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Makes event args to use
     *
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return \TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract
     * @throws \RuntimeException
     */
    public function makeEventArgs(array $params, array $optionOverrides = [], array $frontEndOptions=[]): GenericEventArgsContract
    {
        $options = $this->getOptions();
        /** @noinspection NullPointerExceptionInspection */
        $entitiesShareConfigs = $this->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'entitiesShareConfigs');

        return new GenericEventArgs(new \ArrayObject([
            'params'=>$params,
            'arrayHelper'=>$this->getArrayHelper(),
            'configArrayHelper'=>$this->getConfigArrayHelper(),
            'results'=>[],
            'self'=>$this,
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'entitiesShareConfigs'=>$entitiesShareConfigs,
            'frontEndOptions'=>$frontEndOptions,
        ]));
    }

    /**
     * Creates a query builder wrapper
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \TempestTools\Scribe\Exceptions\Orm\RepositoryException
     */
    public function createQueryWrapper(string $entityAlias = null):QueryBuilderWrapperContract
    {
        $entityAlias = $entityAlias ?? $this->getEntityAlias();
        /** @noinspection NullPointerExceptionInspection */
        $queryType = $this->getConfigArrayHelper()->getArray()['read']['settings']['queryType'] ?? 'dql';
        if ($queryType === 'dql') {
            $qb = $this->createQueryBuilder($entityAlias);
            $qbWrapper = new QueryBuilderDqlWrapper($qb);
        } else if ($queryType === 'sql') {
            $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
            $qbWrapper = new QueryBuilderSqlWrapper($qb);
        } else {
            throw RepositoryException::queryTypeNotRecognized($queryType);
        }

        return $qbWrapper;
    }

    /**
     * Gets the class name for entities that use this repo
     * @return string
     * @throws \RuntimeException
     */
    public function getClassNameBase():string
    {
        return $this->getClassName();
    }

    /**
     * Gets the class name for entities that use this repo
     * @return string
     */
    public function getEntityNameBase(): string
    {
        return $this->getEntityName();
    }

    /**
     * Convenience method for find in functionality
     * @param string $fieldName
     * @param array $values
     * @return mixed
     */
    public function findIn(string $fieldName, array $values):array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getClassName(), 'e')
            ->where($qb->expr()->in('e.'.$fieldName, ':in'));
        $qb->setParameter(':in', $values);
        return $qb->getQuery()->getResult();
    }

}
?>