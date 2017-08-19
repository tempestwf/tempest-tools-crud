<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 6:37 PM
 */

namespace TempestTools\Crud\Orm;


use TempestTools\Crud\Contracts\EventManagerWrapperContract;

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
     * @param EventManagerWrapperContract $eventManagerWrapper
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
     * @return EventManagerWrapperContract
     * @throws \RuntimeException
     */
    abstract protected function createEventManagerWrapper ():EventManagerWrapperContract;
}