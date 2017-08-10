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
     * @param array $frontEndOptions
     * @param array $options
     * @param array $optionOverrides
     * @return array
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(QueryBuilder $qb, array $params, array $frontEndOptions, array $options, array $optionOverrides):array;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    public function prepareResult (QueryBuilder $qb, array $extra):array;
    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilder $qb, array $extra):void;

    /**
     * @param int $limit
     * @param array $extra
     * @internal param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyLimitAndOffset (int $limit, array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilder $qb, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param string $key
     * @param array $value
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyFrontEndGroupBys (string $key, array $value, array $permissions, array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilder $qb, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $key
     * @param array $value
     * @param array $permissions
     * @param $extra
     * @throws \RuntimeException
     */
    public function verifyFrontEndOrderBys (string $key, array $value, array $permissions, $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilder $qb, array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilder $qb, array $extra):void;

    /**
     * @param array $condition
     * @param array $permissions
     * @throws \RuntimeException
     */
    public function verifyFrontEndCondition (array $condition, array $permissions):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilder $qb, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $key
     * @param array $value
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyPlaceholders (string $key, array $value, array $permissions , array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilder $qb, array $extra):void;

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilder $qb, array $extra):void;

    /**
     * @param $key
     * @param array $settings
     * @param array $extra
     * @return array
     */
    public function closureMutate (string $key=null, array $settings=null, array $extra):array;
    /**
     * @param array $permissions
     * @param array $extra
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function closurePermission (array $permissions, array $extra, bool $noisy = true):bool;

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool;

}
?>