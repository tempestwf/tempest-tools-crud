<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Scribe\Exceptions\Orm\Helper;

/**
 * Exception for errors that can happen on the data bind helper.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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

    /**
     * @param string $fieldName
     * @param string $entityName
     * @return DataBindHelperException
     * @internal param int $count
     * @internal param int $max
     */
    public static function propertyNotAField (string $fieldName, string $entityName): DataBindHelperException
    {
        return new self (sprintf('Error: You attempted to access a property of an entity that wasn\'t a field. entity name = %s, property name = %s', $entityName, $fieldName));
    }


}



