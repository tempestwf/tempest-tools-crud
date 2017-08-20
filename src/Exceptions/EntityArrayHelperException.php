<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions;


class EntityArrayHelperException extends \RunTimeException
{
    /**
     * @param string $arg1
     * @param string $arg2
     * @return EntityArrayHelperException
     */
    public static function chainTypeNotAllow (string $arg1, string $arg2): EntityArrayHelperException
    {
        return new self (sprintf('Error: Requested chain type not permitted. chainType = %s, relationName = %s.', $arg1, $arg2));
    }

    /**
     * @param string $arg1
     * @param string $arg2
     * @return EntityArrayHelperException
     */
    public static function assignTypeNotAllow (string $arg1, string $arg2): EntityArrayHelperException
    {
        return new self (sprintf('Error: Requested assign type not permitted. assignType = %s, fieldName = %s.', $arg1, $arg2));
    }

    /**
     * @return EntityArrayHelperException
     */
    public static function actionNotAllow (): EntityArrayHelperException
    {
        return new self (sprintf('Error: the requested action is not allowed on this entity for this request.'));
    }

    /**
     * @param string $arg1
     * @return EntityArrayHelperException
     */
    public static function enforcementFails (string $arg1 = null): EntityArrayHelperException
    {
        return new self (sprintf('Error: A field is not set to it\'s enforced value. fieldName = %s.', $arg1));
    }

    /**
     * @param string $arg1
     * @return EntityArrayHelperException
     */
    public static function closureFails (string $arg1 = null): EntityArrayHelperException
    {
        return new self (sprintf('Error: A validation closure did not pass. fieldName = %s.', $arg1));
    }

    /**
     * @param string $arg1
     * @return EntityArrayHelperException
     */
    public static function assignTypeMustBe (string $arg1): EntityArrayHelperException
    {
        return new self (sprintf('Error: Assign type must be set, add or remove. assignType = %s', $arg1));
    }


}



