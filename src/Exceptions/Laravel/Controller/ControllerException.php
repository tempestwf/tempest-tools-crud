<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/26/2017
 * Time: 8:00 PM
 */

namespace TempestTools\Crud\Exceptions\Laravel\Controller;

/**
 * Exception for errors that can happen on a controller.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class ControllerException extends \RunTimeException
{
    /**
     * @param string $method
     * @return ControllerException
     */
    public static function methodNotImplemented (string $method): ControllerException
    {
        return new self (sprintf('Error: Method not implemented on controller. method = %s', $method));
    }
}