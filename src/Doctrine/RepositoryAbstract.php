<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use TempestTools\Crud\Contracts\HasPathAndFallBackContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Utility\CreateEventManagerWrapperTrait;
use TempestTools\Crud\Doctrine\Wrapper\EntityManagerWrapper;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderDqlWrapper;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderSqlWrapper;
use TempestTools\Crud\Exceptions\Orm\RepositoryException;
use TempestTools\Crud\Orm\RepositoryCoreTrait;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, RepositoryContract, HasPathAndFallBackContract
{
    use RepositoryCoreTrait, CreateEventManagerWrapperTrait;

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
        'clearPrePopulatedEntitiesOnFlush'=>true
    ];

    /**
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
     * @return \TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract
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
            'results'=>[],
            'self'=>$this,
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'entitiesShareConfigs'=>$entitiesShareConfigs,
            'frontEndOptions'=>$frontEndOptions,
        ]));
    }

    /**
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \TempestTools\Crud\Exceptions\Orm\RepositoryException
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
     * @return string
     * @throws \RuntimeException
     */
    public function getClassNameBase():string
    {
        return $this->getClassName();
    }

    /**
     * @return string
     */
    public function getEntityNameBase(): string
    {
        return $this->getEntityName();
    }

    /**
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