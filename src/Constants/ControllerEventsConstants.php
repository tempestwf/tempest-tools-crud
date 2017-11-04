<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 10/2/2017
 * Time: 6:03 PM
 */

namespace TempestTools\Scribe\Constants;


use TempestTools\Scribe\Laravel\Events\Controller\Init;
use TempestTools\Scribe\Laravel\Events\Controller\PostDestroy;
use TempestTools\Scribe\Laravel\Events\Controller\PostIndex;
use TempestTools\Scribe\Laravel\Events\Controller\PostShow;
use TempestTools\Scribe\Laravel\Events\Controller\PostStore;
use TempestTools\Scribe\Laravel\Events\Controller\PostUpdate;
use TempestTools\Scribe\Laravel\Events\Controller\PreDestroy;
use TempestTools\Scribe\Laravel\Events\Controller\PreIndex;
use TempestTools\Scribe\Laravel\Events\Controller\PreShow;
use TempestTools\Scribe\Laravel\Events\Controller\PreStore;
use TempestTools\Scribe\Laravel\Events\Controller\PreUpdate;

/**
 * Constants related to events that will be fired on controllers.
 * Each constant contains a link to the event that is fired, and a reference to the name of the method that will listen for that event on the controller.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class ControllerEventsConstants
{
    /**
     * Information on the Init event.
     */
    const INIT = [
        'class'=>Init::class,
        'on'=>'onInit'
    ];
    /**
     * Information on the postDestroy event.
     */
    const POST_DESTROY = [
        'class'=>PostDestroy::class,
        'on'=>'onPostDestroy'
    ];
    /**
     * Information on the postIndex event.
     */
    const POST_INDEX = [
        'class'=>PostIndex::class,
        'on'=>'onPostIndex'
    ];
    /**
     * Information on the postShow event.
     */
    const POST_SHOW = [
        'class'=>PostShow::class,
        'on'=>'onPostShow'
    ];
    /**
     * Information on the postStore event.
     */
    const POST_STORE = [
        'class'=>PostStore::class,
        'on'=>'onPostStore'
    ];
    /**
     * Information on the postUpdate event.
     */
    const POST_UPDATE = [
        'class'=>PostUpdate::class,
        'on'=>'onPostUpdate'
    ];

    /**
     * Information on the preDestroy event.
     */
    const PRE_DESTROY = [
        'class'=>PreDestroy::class,
        'on'=>'onPreDestroy'
    ];

    /**
     * Information on the preIndex event.
     */
    const PRE_INDEX = [
        'class'=>PreIndex::class,
        'on'=>'onPreIndex'
    ];
    /**
     * Information on the preShow event.
     */
    const PRE_SHOW = [
        'class'=>PreShow::class,
        'on'=>'onPreShow'
    ];
    /**
     * Information on the preStore event.
     */
    const PRE_STORE = [
        'class'=>PreStore::class,
        'on'=>'onPreStore'
    ];
    /**
     * Information on the preUpdate event.
     */
    const PRE_UPDATE = [
        'class'=>PreUpdate::class,
        'on'=>'onPreUpdate'
    ];

    /**
     * Gets information on all the events.
     * @return array
     */
    public static function getAll():array
    {
        return [
            static::INIT,
            static::POST_DESTROY,
            static::POST_INDEX,
            static::POST_SHOW,
            static::POST_STORE,
            static::POST_UPDATE,
            static::PRE_DESTROY,
            static::PRE_INDEX,
            static::PRE_SHOW,
            static::PRE_STORE,
            static::PRE_UPDATE,
        ];
    }

}