<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 4:33 PM
 */

namespace TempestTools\Crud\Doctrine\Wrapper;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException;

class QueryBuilderDqlWrapper implements QueryBuilderWrapperContract
{
    /**
     * FIELD_REGEX
     */
    const FIELD_REGEX = '/^\w+\.\w+$/';

    /**
     * SAFE_OPERATORS
     */
    const SAFE_OPERATORS = ['andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between' ];

    /**
     * ORDER_BY_DIRECTIONS
     */
    const ORDER_BY_DIRECTIONS = ['ASC', 'DESC'];

    /** @var BaseQueryBuilder $queryBuilder*/
    protected $queryBuilder;

    /**
     * QueryBuilderConstructionHelper constructor.
     *
     * @param BaseQueryBuilder $queryBuilder
     */
    public function __construct(BaseQueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param bool $paginate
     * @param bool $returnCount
     * @param int|null $hydrationType
     * @param bool $fetchJoin
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function getResult(bool $paginate=false, bool $returnCount=true, int $hydrationType=1, bool $fetchJoin = false)
    {
        $count = null;
        if ($paginate === true) {
            $paginator = new Paginator($this->getQueryBuilder()->getQuery());
            $paginator->getQuery()->setHydrationMode($hydrationType);
            $count = $returnCount?count($paginator, $fetchJoin):null;
            $result = $paginator->getIterator()->getArrayCopy();
        } else {
            if ($count === true) {
                throw QueryBuilderWrapperException::countRequiresPaginator();
            }
            $this->getQueryBuilder()->getQuery()->setHydrationMode($hydrationType);
            $result = $this->getQueryBuilder()->getQuery()->getResult();
        }
        return ['count'=>$count, 'result'=>$result];
    }

    /**
     * @param string $string
     * @param bool $add
     */
    public function groupBy (string $string, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->groupBy($string);
        } else {
            $this->getQueryBuilder()->addGroupBy($string);
        }
    }

    /**
     * @param string $sort
     * @param string $order
     * @param bool $add
     */
    public function orderBy (string $sort, string $order, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->orderBy($sort, $order);
        } else {
            $this->getQueryBuilder()->addOrderBy($sort, $order);
        }

    }

    /**
     * @param string $type
     * @param string $string
     * @param bool $add
     */
    public function where(string $type=null, string $string, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->where($string);
        } else {
            $type = $type === null || $type === 'and'?'andWhere':'orWhere';
            $this->getQueryBuilder()->$type($string);
        }

    }

    /**
     * @param string $type
     * @param string $string
     * @param bool $add
     */
    public function having(string $type=null, string $string, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->having($string);
        } else {
            $type = $type === null || $type === 'and'?'andHaving':'orHaving';
            $this->getQueryBuilder()->$type($string);
        }
    }

    /**
     * @param string $expr
     * @param array $arguments
     * @return string
     */
    public function useExpression(string $expr, array $arguments):string
    {
        return call_user_func_array ([$this->getQueryBuilder()->expr(), $expr], $arguments);
    }

    /**
     * @param string $placeholderName
     * @param $argument
     * @param null $type
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function setParameter(string $placeholderName, $argument, $type=null):void
    {
        if ($type !== null && Type::hasType($type) !== true) {
            throw QueryBuilderWrapperException::parameterTypeNotSupported($type);
        }
        $this->getQueryBuilder()->setParameter($placeholderName, $argument, $type);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param bool $useQueryCache
     * @param bool $useResultCache
     * @param int|null $timeToLive
     * @param string|null $cacheId
     * @param null $queryCacheDriver
     * @param null $resultCacheDriver
     * @throws \Doctrine\ORM\ORMException
     */
    public function setCacheSettings (bool $useQueryCache=true, bool $useResultCache = false, int $timeToLive=null, string $cacheId = null, $queryCacheDriver= null, $resultCacheDriver = null):void
    {
        $this->getQueryBuilder()->getQuery()->useQueryCache($useQueryCache);

        $this->getQueryBuilder()->getQuery()->useResultCache($useResultCache, $timeToLive, $cacheId);
        if ($queryCacheDriver !== null) {
            $this->getQueryBuilder()->getQuery()->setQueryCacheDriver($queryCacheDriver);
        }
        if ($resultCacheDriver !== null) {
            $this->getQueryBuilder()->getQuery()->setResultCacheDriver($resultCacheDriver);
        }
    }

    /**
     * @param string $string
     * @param bool $add
     */
    public function select (string $string, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->select($string);
        } else {
            $this->getQueryBuilder()->addSelect($string);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $className
     * @param string $alias
     * @param string|null $indexBy
     * @param bool $add
     */
    public function from(string $className, string $alias, string $indexBy=null, bool $add=false): void
    {
        if ($add === false) {
            /** @noinspection PhpParamsInspection */
            $this->getQueryBuilder()->add('from', new Expr\From($className, $alias, $indexBy));
        } else {
            $this->getQueryBuilder()->from($className, $alias, $indexBy);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     */
    public function leftJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void
    {
        $this->getQueryBuilder()->leftJoin($join, $alias, $conditionType, $condition, $indexBy);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     */
    public function innerJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void
    {
        $this->getQueryBuilder()->innerJoin($join, $alias, $conditionType, $condition, $indexBy);
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    public function setLimitAndOffset(int $limit, int $offset):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getQueryBuilder()->setMaxResults($limit);
        /** @noinspection NullPointerExceptionInspection */
        $this->getQueryBuilder()->setFirstResult($offset);
    }

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool
    {
        $fieldFormatOk = preg_match(static::FIELD_REGEX, $field);
        if ($fieldFormatOk === false) {
            if ($noisy === false) {
                throw QueryBuilderWrapperException::fieldBadlyFormed($field);
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $direction
     * @param bool $noisy
     * @return bool
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function verifyDirectionFormat (string $direction, bool $noisy = true):bool
    {
        $directionOk = in_array($direction, static::ORDER_BY_DIRECTIONS, true);
        if ($directionOk === false) {
            if ($noisy === false) {
                throw QueryBuilderWrapperException::directionNotAllow($direction);
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $operator
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function verifyOperatorAllowed(string $operator):void
    {
        if (!in_array($operator, static::SAFE_OPERATORS, true)) {
            throw QueryBuilderWrapperException::operatorNotSafe($operator);
        }
    }


    /**
     * @return BaseQueryBuilder
     */
    public function getQueryBuilder():BaseQueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @param BaseQueryBuilder $queryBuilder
     */
    public function setQueryBuilder(BaseQueryBuilder $queryBuilder):void
    {
        $this->queryBuilder = $queryBuilder;
    }


}