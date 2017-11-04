<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/14/2017
 * Time: 6:23 PM
 */

namespace TempestTools\Scribe\Contracts\Orm\Events;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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