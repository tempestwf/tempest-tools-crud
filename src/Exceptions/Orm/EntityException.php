<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Scribe\Exceptions\Orm;

/**
 * Exception for errors that can happen on an entity
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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



