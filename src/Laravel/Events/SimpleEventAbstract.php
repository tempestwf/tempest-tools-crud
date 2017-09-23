<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 7:02 PM
 */

namespace TempestTools\Crud\Laravel\Events;


use ArrayObject;

abstract class SimpleEventAbstract
{
    /** @var  ArrayObject  $eventArgs*/
    protected $eventArgs;

    public function __construct(ArrayObject $eventArgs)
    {
        $this->setEventArgs($eventArgs);
    }

    /**
     * @return ArrayObject
     */
    public function getEventArgs(): ArrayObject
    {
        return $this->eventArgs;
    }

    /**
     * @param ArrayObject $eventArgs
     */
    public function setEventArgs(ArrayObject $eventArgs):void
    {
        $this->eventArgs = $eventArgs;
    }
}