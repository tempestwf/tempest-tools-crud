<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/29/2017
 * Time: 5:32 PM
 */

namespace TempestTools\Scribe\Contracts\Events;

use ArrayObject;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface SimpleEventContract
{
    /**
     * @return ArrayObject
     */
    public function getEventArgs(): ArrayObject;

    /**
     * @param ArrayObject $eventArgs
     */
    public function setEventArgs(ArrayObject $eventArgs): void;
}