<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 8:28 PM
 */

namespace TempestTools\Crud\Doctrine;


use TempestTools\Crud\Contracts\EventManagerWrapperContract;
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