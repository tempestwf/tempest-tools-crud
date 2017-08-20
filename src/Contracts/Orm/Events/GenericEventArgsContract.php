<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 6:23 PM
 */

namespace TempestTools\Crud\Contracts\Orm\Events;


interface GenericEventArgsContract
{
    /**
     * @param \ArrayObject $args
     * @return GenericEventArgsContract
     */
    public function setArgs(\ArrayObject $args): GenericEventArgsContract;

    /**
     * @return \ArrayObject
     */
    public function getArgs(): \ArrayObject;
}