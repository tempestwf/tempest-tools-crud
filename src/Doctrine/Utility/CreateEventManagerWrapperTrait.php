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