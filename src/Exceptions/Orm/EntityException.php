<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions\Orm;


class EntityException extends \RunTimeException
{

    /**
     * @return EntityException
     */
    public static function prePersistValidatorFails (): EntityException
    {
        return new self (sprintf('Error: Validation failed on pre-persist.'));
    }

}



