<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Scribe\Exceptions\Orm\Helper;

/**
 * Exception for errors that can happen on the query builder helper.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class QueryBuilderHelperException extends \RunTimeException
{

    /**
     * @param string $arg
     * @return QueryBuilderHelperException
     */
    public static function placeholderNoAllowed (string $arg): QueryBuilderHelperException
    {
        return new self (sprintf('Error: You do not have access requested placeholder. placeholder = %s', $arg));
    }

    /**
     * @param string $arg
     * @param string $arg2
     * @return QueryBuilderHelperException
     */
    public static function operatorNotAllowed (string $arg, string $arg2): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Operator not allowed. field = %s, operator = %s', $arg, $arg2));
    }

    /**
     * @param string $arg
     * @param string $arg2
     * @return QueryBuilderHelperException
     */
    public static function orderByNotAllowed (string $arg, string $arg2): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Order by not allowed. field = %s, direction = %s', $arg, $arg2));
    }

    /**
     * @param string $arg
     * @return QueryBuilderHelperException
     */
    public static function groupByNotAllowed (string $arg): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Group by not allowed. field = %s', $arg));
    }

    /**
     * @param int $arg
     * @param int $arg2
     * @param int $arg3
     * @return QueryBuilderHelperException
     */
    public static function fixedLimit (int $arg, int $arg2, int $arg3): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Requested limit and offset are not divisible by the fixed limit. limit = %s, offset = %s, fixed limit = %s', $arg, $arg2, $arg3));
    }

    /**
     * @param int $arg
     * @param int $arg2
     * @return QueryBuilderHelperException
     */
    public static function maxLimitHit (int $arg, int $arg2): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Requested limit greater than max. limit = %s, max = %s', $arg, $arg2));
    }

    /**
     * @return QueryBuilderHelperException
     */
    public static function closureFails (): QueryBuilderHelperException
    {
        return new self (sprintf('Error: A validation closure did not pass while building query.'));
    }

    /**
     * @return QueryBuilderHelperException
     */
    public static function readRequestNotAllowed (): QueryBuilderHelperException
    {
        return new self (sprintf('Error: Read request not allowed.'));
    }

    /**
     * @param int $count
     * @param int $max
     * @return QueryBuilderHelperException
     */
    public static function moreQueryParamsThanMax (int $count, int $max): QueryBuilderHelperException
    {
        return new self (sprintf('Error: More query params than passed than permitted. count = %s, max = %s', $count, $max));
    }
}



