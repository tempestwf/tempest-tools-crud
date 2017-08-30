<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 4:33 PM
 */

namespace TempestTools\Crud\Doctrine\Wrapper;

use Doctrine\DBAL\Query\QueryBuilder;

use TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException;

class QueryBuilderSqlWrapper extends QueryBuilderDqlWrapper
{
    /** @var QueryBuilder $queryBuilder*/
    /** @noinspection ClassOverridesFieldOfSuperClassInspection */
    protected $queryBuilder;

    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    /** @noinspection SenselessMethodDuplicationInspection */
    /**
     * QueryBuilderConstructionHelper constructor.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }
    /** @noinspection PhpHierarchyChecksInspection */

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder():QueryBuilder
    {
        return $this->queryBuilder;
    }

    /** @noinspection SenselessMethodDuplicationInspection */
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */ QueryBuilder $queryBuilder):void
    {
        $this->queryBuilder = $queryBuilder;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param bool $paginate
     * @param bool $returnCount
     * @param int|null $hydrationType
     * @param bool $fetchJoin
     * @param array $cacheSettings
     * @param bool $hydrate
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Wrapper\QueryBuilderWrapperException
     */
    public function getResult(bool $paginate=false, bool $returnCount=true, int $hydrationType=null, bool $fetchJoin = false, array $cacheSettings, bool $hydrate)
    {
        $count = null;
        if ($paginate === true) {
            throw QueryBuilderWrapperException::paginationNotCompatible();
        }

        if ($hydrationType === null) {
            throw QueryBuilderWrapperException::hydrationNotCompatible();
        }


        $qb = $this->getQueryBuilder();
        if ($hydrate === true) {
            $result = $qb->execute();
            $count = $returnCount?$qb->setFirstResult(0)->setMaxResults(null)->execute()->rowCount():null;
            return ['count'=>$count, 'result'=>$result];
        }

        return [
            'qb'=>$qb,
            'qbWrapper'=>$this
        ];

    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $className
     * @param string $alias
     * @param string|null $indexBy
     * @param bool $add
     * @throws QueryBuilderWrapperException
     */
    public function from(string $className, string $alias, string $indexBy=null, bool $add=false): void
    {
        if ($indexBy !== null) {
            throw QueryBuilderWrapperException::indexByNotCompatible();
        }

        if ($add === false) {
            /** @noinspection PhpParamsInspection */
            $this->getQueryBuilder()->$this->add('from', array(
                'table' => $className,
                'alias' => $alias
            ), false);
        } else {
            $this->getQueryBuilder()->from($className, $alias);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     * @throws QueryBuilderWrapperException
     */
    public function leftJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void
    {
        if ($indexBy !== null) {
            throw QueryBuilderWrapperException::indexByNotCompatible();
        }
        $this->getQueryBuilder()->leftJoin($join, $alias, $conditionType, $condition);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     * @throws QueryBuilderWrapperException
     */
    public function innerJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void
    {
        if ($indexBy !== null) {
            throw QueryBuilderWrapperException::indexByNotCompatible();
        }
        $this->getQueryBuilder()->innerJoin($join, $alias, $conditionType, $condition);
    }


    /**
     * @param string $operator
     * @throws QueryBuilderWrapperException
     */
    public function verifyOperatorAllowed(string $operator):void
    {
        if (!in_array($operator, static::SAFE_OPERATORS, true)) {
            throw QueryBuilderWrapperException::operatorNotSafe($operator);
        }
    }

}