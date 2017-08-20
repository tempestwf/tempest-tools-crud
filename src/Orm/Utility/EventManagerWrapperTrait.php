<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 6:37 PM
 */

namespace TempestTools\Crud\Orm\Utility;


use TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract;

trait EventManagerWrapperTrait
{
    /** @var  EventManagerWrapperContract  $eventManager*/
    protected $eventManager;

    /**
     * @return EventManagerWrapperContract
     */
    public function getEventManager(): EventManagerWrapperContract
    {
        return $this->eventManager;
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract $eventManagerWrapper
     */
    public function setEventManager(EventManagerWrapperContract $eventManagerWrapper):void
    {
        $this->eventManager = $eventManagerWrapper;
    }

    /**
     * @param bool $force
     * @throws \RuntimeException
     */
    protected function eventManagerInit(bool $force= true):void
    {
        if ($force === true || $this->getEventManager() === null) {
            $this->setEventManager($this->createEventManagerWrapper());
            /** @noinspection PhpParamsInspection */
            $this->getEventManager()->addEventSubscriber($this);
        }
    }

    /**
     * @return \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract
     * @throws \RuntimeException
     */
    abstract protected function createEventManagerWrapper ():EventManagerWrapperContract;
}