<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 7:02 PM
 */

namespace TempestTools\Scribe\Laravel\Events;


use ArrayObject;
use \TempestTools\Scribe\Contracts\Events\SimpleEventContract;

/**
 * An abstract class to be extended by events. This allows the event to have an array object of "arguments" assigned to it.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
abstract class SimpleEventAbstract implements SimpleEventContract
{
    /** @var  ArrayObject  $eventArgs*/
    protected $eventArgs;

    /**
     * SimpleEventAbstract constructor.
     *
     * @param ArrayObject $eventArgs
     */
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