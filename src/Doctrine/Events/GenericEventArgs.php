<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/12/2017
 * Time: 8:31 PM
 */

namespace TempestTools\Scribe\Doctrine\Events;


use Doctrine\Common\EventArgs;
use TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract;

/**
 * A class for a simple object that stores arguments for events.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class GenericEventArgs extends EventArgs implements GenericEventArgsContract
{
    /** @var \ArrayObject */
    public $args;

    /**
     * GenericEventArgs constructor.
     *
     * @param \ArrayObject $args
     */
    public function __construct(\ArrayObject $args) {
        $this->setArgs($args);
    }
    /**
     * @param \ArrayObject $args
     * @return \TempestTools\Scribe\Contracts\Orm\Events\GenericEventArgsContract
     */
    public function setArgs(\ArrayObject $args): GenericEventArgsContract
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return \ArrayObject
     */
    public function getArgs(): \ArrayObject
    {
        return $this->args;
    }
}