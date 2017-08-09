<?php
namespace TempestTools\Crud\Contracts;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelper;
use Doctrine\ORM\QueryBuilder;

interface QueryHelper extends ArrayHelper {
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @param array $options
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return array
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(QueryBuilder $qb, array $params, array $options, array $optionOverrides, array $frontEndOptions):array;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     * @throws RuntimeException
     */
    public function prepareResult (QueryBuilder $qb, array $extra):array;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addLimitAndOffset(QueryBuilder $qb, array $extra, bool $verify = true):void;
    /**
     * @param array $extra
     * @throws RuntimeException
     * @internal param array $extra
     */
    public function verifyLimitAndOffset (array $extra):void;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilder $qb, array $extra, bool $verify = true):void;

    /**
     * @param array $extra
     * @throws RuntimeException
     * @internal param array $extra
     */
    public function verifyFrontEndGroupBys (array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilder $qb, array $extra, bool $verify = true):void;

    /**
     * @param $extra
     * @throws RuntimeException
     */
    public function verifyFrontEndOrderBys ($extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addFrontEndWhere(QueryBuilder $qb, array $extra, bool $verify = true):void;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addFrontEndHaving(QueryBuilder $qb, array $extra, bool $verify = true):void;
    /**
     * @param array $conditions
     * @param array $permissions
     * @throws RuntimeException
     * @internal param string $part
     */
    public function verifyFrontEndConditions (array $conditions, array $permissions):void;

    /**
     * @param array $condition
     * @param array $permissions
     * @throws RuntimeException
     */
    public function verifyFrontEndCondition (array $condition, array $permissions):void;

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws RuntimeException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool;
    /**
     * @param array $extra
     * @throws RuntimeException
     * @internal param array $extra
     */
    public function verifyPlaceholders (array $extra):void;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws RuntimeException
     */
    public function addPlaceholders(QueryBuilder $qb, array $extra, bool $verify = true);

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilder $qb, array $extra);

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilder $qb, array $extra):void;

}
?>