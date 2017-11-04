<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 6:23 PM
 */

namespace TempestTools\Scribe\Contracts\Orm\Wrapper;


/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface QueryBuilderWrapperContract
{

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param bool $paginate
     * @param bool $returnCount
     * @param int|null $hydrationType
     * @param bool $fetchJoin
     * @param array $cacheSettings
     * @param bool $hydrate
     * @return mixed
     */
    public function getResult(bool $paginate=false, bool $returnCount=true, int $hydrationType=1, bool $fetchJoin = false, array $cacheSettings, bool $hydrate);
    /**
     * @param string $string
     * @param bool $add
     */
    public function groupBy (string $string, bool $add = true):void;
    /**
     * @param string $sort
     * @param string $order
     * @param bool $add
     */
    public function orderBy (string $sort, string $order, bool $add = true):void;

    /**
     * @param string $type
     * @param string $string
     * @param bool $add
     */
    public function where(string $type=null, string $string, bool $add = true):void;
    /**
     * @param string $type
     * @param string $string
     * @param bool $add
     */
    public function having(string $type=null, string $string, bool $add = true):void;
    /**
     * @param string $expr
     * @param array $arguments
     * @return string
     */
    public function useExpression(string $expr, array $arguments):string;

    /**
     * @param string $placeholderName
     * @param $argument
     * @param null $type
     */
    public function setParameter(string $placeholderName, $argument, $type=null):void;

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param string $string
     * @param bool $add
     */
    public function select (string $string, bool $add = true):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $className
     * @param string $alias
     * @param string|null $indexBy
     * @param bool $add
     */
    public function from(string $className, string $alias, string $indexBy=null, bool $add=false): void;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     */
    public function leftJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $join
     * @param string $alias
     * @param string|null $conditionType
     * @param string|null $condition
     * @param string|null $indexBy
     */
    public function innerJoin(string $join, string $alias, string $conditionType = null, string $condition = null, string $indexBy = null):void;

    /**
     * @param int $limit
     * @param int $offset
     */
    public function setLimitAndOffset(int $limit, int $offset):void;

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool;

    /**
     * @param string $direction
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyDirectionFormat (string $direction, bool $noisy = true):bool;

    /**
     * @param string $operator
     * @throws \RuntimeException
     */
    public function verifyOperatorAllowed(string $operator):void;

}