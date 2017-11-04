<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 8:28 PM
 */

namespace TempestTools\Scribe\Doctrine\Utility;


use TempestTools\Scribe\Contracts\Orm\Wrapper\EventManagerWrapperContract;
use TempestTools\Scribe\Doctrine\Wrapper\EventManagerWrapper;

/**
 * A trait which allows a class to return an event manager wrapper.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
trait CreateEventManagerWrapperTrait
{
    /**
     * @return EventManagerWrapperContract
     * @throws \RuntimeException
     */
    public function createEventManagerWrapper ():EventManagerWrapperContract
    {
        return new EventManagerWrapper();
    }
}