<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 6:37 PM
 */

namespace TempestTools\Crud\Orm\Utility;


use TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract;

/**
 * A trait that adds convenience methods to a class for accessing an event manager wrapper
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
trait EventManagerWrapperTrait
{
    /** @var  EventManagerWrapperContract  $eventManager*/
    protected $eventManager;

    /**
     * @return EventManagerWrapperContract|null
     */
    public function getEventManager(): ?EventManagerWrapperContract
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
            $eventManager = $this->createEventManagerWrapper();
            /** @noinspection PhpParamsInspection */
            $eventManager->addEventSubscriber($this);
            $this->setEventManager($eventManager);

        }
    }

    /**
     * @return \TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract
     * @throws \RuntimeException
     */
    abstract protected function createEventManagerWrapper ():EventManagerWrapperContract;
}