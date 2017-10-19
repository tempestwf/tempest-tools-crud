<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/26/2017
 * Time: 8:00 PM
 */

namespace TempestTools\Crud\Exceptions\Laravel\Controller;


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