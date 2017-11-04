<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 4:33 PM
 */

namespace TempestTools\Scribe\Doctrine\Wrapper;

use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException;
use Doctrine\ORM\Query;

/**
 * A wrapper class to provide a universal interface for accessing Doctrine Dql Query Builder functionality.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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
     * Gets the result for the query form the db
     * @param bool $paginate
     * @param bool $returnCount
     * @param int|null $hydrationType
     * @param bool $fetchJoin
     * @param array $cacheSettings
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function getResult(bool $paginate=false, bool $returnCount=true, int $hydrationType=1, bool $fetchJoin = false, array $cacheSettings, bool $hydrate)
    {
        $count = null;
        $query = $this->getQueryBuilder()->getQuery();
        $paginator = null;
        if ($paginate === true) {
            $paginator = new Paginator($query, $fetchJoin);
            $query = $paginator->getQuery();
            $query->setHydrationMode($hydrationType);
        } else {
            if ($count === true) {
                throw QueryBuilderWrapperException::countRequiresPaginator();
            }
            $query->setHydrationMode($hydrationType);
        }

        if (count($cacheSettings) > 0) {
            $this->setCacheSettings($query, $cacheSettings['useQueryCache'], $cacheSettings['useResultCache'], $cacheSettings['timeToLive'], $cacheSettings['cacheId'], $cacheSettings['queryCacheDriver'], $cacheSettings['resultCacheDriver']);
        }

        if ($hydrate === true) {
            if ($paginate === true) {
                $count = $returnCount?count($paginator):null;
                $result = $paginator->getIterator()->getArrayCopy();
            } else {
                $result = $query->getResult();
            }
            return ['count'=>$count, 'result'=>$result];
        }

        return [
            'paginator'=>$paginator,
            'query'=>$query,
            'qb'=>$this->getQueryBuilder(),
            'qbWrapper'=>$this
        ];


    }

    /**
     * Adds a group by to the query
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
     * Adds an order by to the query
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
     * Adds a where to the query
     * @param string $type
     * @param string $string
     * @param bool $add
     */
    public function where(string $type=null, string $string, bool $add = true):void
    {
        if ($add === false) {
            $this->getQueryBuilder()->where($string);
        } else {
            $type = $type === null || $type !== 'or'?'andWhere':'orWhere';
            $this->getQueryBuilder()->$type($string);
        }

    }

    /**
     * Adds a having to the query
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
     * Calls an expression on the expression builder and passes it in the required arguments for the expression.
     * @param string $expr
     * @param array $arguments
     * @return string
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function useExpression(string $expr, array $arguments):string
    {
        $this->verifyOperatorAllowed($expr);
        return call_user_func_array ([$this->getQueryBuilder()->expr(), $expr], $arguments);
    }

    /**
     * Sets a parameter in a query
     * @param string $placeholderName
     * @param $argument
     * @param null $type
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
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
     * Sets the cache settings of the query
     * @param Query $query
     * @param bool $useQueryCache
     * @param bool $useResultCache
     * @param int|null $timeToLive
     * @param string|null $cacheId
     * @param Cache|null $queryCacheDriver
     * @param Cache|null $resultCacheDriver
     * @throws \Doctrine\ORM\ORMException
     */
    protected function setCacheSettings (Query $query, bool $useQueryCache=true, bool $useResultCache = false, int $timeToLive=null, string $cacheId = null, Cache $queryCacheDriver= null, Cache $resultCacheDriver = null):void
    {
        $query->useQueryCache($useQueryCache);
        $query->useResultCache($useResultCache, $timeToLive, $cacheId);
        if ($queryCacheDriver !== null) {
            $query->setQueryCacheDriver($queryCacheDriver);
        }
        if ($resultCacheDriver !== null) {
            $query->setResultCacheDriver($resultCacheDriver);
        }
    }

    /**
     * Adds a select to the query
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
     * Adds a from to the query
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
     * Adds a left joint to the query
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
     * Adds an inner join to the query
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
     * Adds a limit and offset to the query
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
     * Verifies that the field format for a field passed from the front end is formed correctly
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
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
     * Verifies that a direction for an order by from the front end is one of the allowable options.
     * @param string $direction
     * @param bool $noisy
     * @return bool
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
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
     * Verifies that the operator for a front end filter is one of the allowed types.
     * @param string $operator
     * @throws \TempestTools\Scribe\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function verifyOperatorAllowed(string $operator):void
    {
        if (!in_array($operator, static::SAFE_OPERATORS, true)) {
            throw QueryBuilderWrapperException::operatorNotSafe($operator);
        }
    }
    /** @noinspection ReturnTypeCanBeDeclaredInspection */


    /**
     * @return BaseQueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }


    /**
     * @param BaseQueryBuilder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder):void
    {
        $this->queryBuilder = $queryBuilder;
    }


}