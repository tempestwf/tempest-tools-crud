<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 5:54 PM
 */

namespace TempestTools\Crud\Doctrine\Wrapper;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use TempestTools\Common\Utility\EvmTrait;
use TempestTools\Crud\Contracts\Orm\Wrapper\EventManagerWrapperContract;
use TempestTools\Crud\Contracts\Orm\Events\GenericEventArgsContract;

/**
 * A wrapper class to provide a universal interface for accessing Doctrine Event Manager functionality.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class EventManagerWrapper implements EventManagerWrapperContract
{
    use EvmTrait;

    /**
     * EventManagerWrapper constructor.
     *
     * @param EventManager|null $eventManager
     */
    public function __construct(EventManager $eventManager = null)
    {
        $eventManager = $eventManager ?? new EventManager();
        $this->setEvm($eventManager);
    }

    /**
     * Adds an event subscriber
     * @param EventSubscriber $target
     */
    public function addEventSubscriber (EventSubscriber $target):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $this->getEvm()->addEventSubscriber($target);
    }

    /**
     * Dispatches an event
     * @param string $event
     * @param GenericEventArgsContract $args
     */
    public function dispatchEvent (string $event, GenericEventArgsContract $args):void
    {
        $evm = $this->getEvm();
        $evm->dispatchEvent($event, $args);
    }



}