<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use TempestTools\Crud\Contracts\EntityManagerWrapperContract;
use TempestTools\Crud\Contracts\GenericEventArgsContract;
use TempestTools\Crud\Contracts\QueryBuilderWrapperContract;
use TempestTools\Crud\Contracts\RepositoryContract;
use TempestTools\Crud\Doctrine\Events\GenericEventArgs;
use TempestTools\Crud\Doctrine\Wrapper\EntityManagerWrapper;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderDqlWrapper;
use TempestTools\Crud\Doctrine\Wrapper\QueryBuilderSqlWrapper;
use TempestTools\Crud\Orm\RepositoryCoreTrait;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, RepositoryContract
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
    ];


    /**
     * ERRORS
     */
    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.',
        ],
        'entityToBindNotFound'=>[
            'message'=>'Error: Entity to bind not found.',
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows. count = %s, max = %s',
        ],
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.',
        ],
        'queryTypeNotRecognized'=>[
            'message'=>'Error: Query type from configuration not recognized. query type = %s'
        ],
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
     * @param array $options
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return GenericEventArgsContract
     */
    public function makeEventArgs(array $params, array $options = [], array $optionOverrides = [], array $frontEndOptions=[]): GenericEventArgsContract
    {
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
            'options'=>$this->getOptions(),
            'optionOverrides'=>$optionOverrides,
            'entitiesShareConfigs'=>$entitiesShareConfigs,
            'frontEndOptions'=>$frontEndOptions,
        ]));
    }

    /**
     * @param string $entityAlias
     * @return QueryBuilderWrapperContract
     * @throws \RuntimeException
     */
    public function createQueryWrapper(string $entityAlias = null):QueryBuilderWrapperContract
    {
        $entityAlias = $entityAlias ?? $this->getEntityAlias();
        /** @noinspection NullPointerExceptionInspection */
        $queryType = $this->getConfigArrayHelper()->getArray()['settings']['queryType'] ?? 'dql';
        if ($queryType === 'dql') {
            $qb = $this->createQueryBuilder($entityAlias);
            $qbWrapper = new QueryBuilderDqlWrapper($qb);
        } else if ($queryType === 'sql') {
            $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
            $qbWrapper = new QueryBuilderSqlWrapper($qb);
        } else {
            throw new \RuntimeException(sprintf($this->getErrorFromConstant('queryTypeNotRecognized')['message'], $queryType));
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