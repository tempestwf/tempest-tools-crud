<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 8:28 PM
 */

namespace TempestTools\Crud\Doctrine\Utility;


use TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract;
use TempestTools\Crud\Doctrine\Wrapper\EventManagerWrapper;

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