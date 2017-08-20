<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions;


class DataBindHelperException extends \RunTimeException
{
    /**
     * @return DataBindHelperException
     */
    public static function wrongTypeOfRepo (): DataBindHelperException
    {
        return new self (sprintf('Error: Wrong type of repo used with chaining.'));
    }

    /**
     * @param int $count
     * @param int $max
     * @return DataBindHelperException
     */
    public static function moreRowsRequestedThanBatchMax (int $count, int $max): DataBindHelperException
    {
        return new self (sprintf('Error: More rows requested than batch max allows. count = %s, max = %s', $count, $max));
    }


}



