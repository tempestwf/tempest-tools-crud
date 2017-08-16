<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 4:33 PM
 */

namespace TempestTools\Crud\Doctrine\Wrapper;

use Doctrine\DBAL\Query\QueryBuilder;


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


    /**
     * @param QueryBuilder $queryBuilder
     */
    /** @noinspection SenselessMethodDuplicationInspection */
    /** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
    public function setQueryBuilder(QueryBuilder $queryBuilder):void
    {
        $this->queryBuilder = $queryBuilder;
    }

}