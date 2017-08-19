<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/12/2017
 * Time: 8:31 PM
 */

namespace TempestTools\Crud\Doctrine\Events;


use Doctrine\Common\EventArgs;
use TempestTools\Crud\Contracts\GenericEventArgsContract;

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
     * @return GenericEventArgsContract
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