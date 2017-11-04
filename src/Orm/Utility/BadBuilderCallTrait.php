<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 5:09 PM
 */

namespace TempestTools\Scribe\Orm\Utility;


use TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException;

/**
 * A trait used on multiple builder classes where an unused builder method call will throw an exception
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
trait BadBuilderCallTrait
{
    /**
     * @param $name
     * @param $arguments
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function __call($name, $arguments):void
    {
        throw EntityArrayHelperException::callToBadBuilderMethod($name);
    }
}