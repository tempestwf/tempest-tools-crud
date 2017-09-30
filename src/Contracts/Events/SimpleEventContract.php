<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/29/2017
 * Time: 5:32 PM
 */

namespace TempestTools\Crud\Contracts\Events;

use ArrayObject;

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