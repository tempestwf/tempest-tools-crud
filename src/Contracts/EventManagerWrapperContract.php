<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 8:48 PM
 */

namespace TempestTools\Crud\Contracts;

use Doctrine\Common\EventSubscriber;

interface EventManagerWrapperContract
{
    /**
     * @param EventSubscriber $target
     */
    public function addEventSubscriber(EventSubscriber $target): void;

    /**
     * @param string $event
     * @param GenericEventArgsContract $args
     */
    public function dispatchEvent(string $event, GenericEventArgsContract $args): void;
}