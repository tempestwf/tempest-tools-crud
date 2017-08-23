<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 5:09 PM
 */

namespace TempestTools\Crud\Orm\Utility;


use TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException;

trait BadBuilderCallTrait
{
    /**
     * @param $name
     * @param $arguments
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function __call($name, $arguments):void
    {
        throw EntityArrayHelperException::callToBadBuilderMethod($name);
    }
}