<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 10/2/2017
 * Time: 6:03 PM
 */

namespace TempestTools\Crud\Constants;


use TempestTools\Crud\Laravel\Events\Controller\Init;
use TempestTools\Crud\Laravel\Events\Controller\PostDestroy;
use TempestTools\Crud\Laravel\Events\Controller\PostIndex;
use TempestTools\Crud\Laravel\Events\Controller\PostShow;
use TempestTools\Crud\Laravel\Events\Controller\PostStore;
use TempestTools\Crud\Laravel\Events\Controller\PostUpdate;
use TempestTools\Crud\Laravel\Events\Controller\PreDestroy;
use TempestTools\Crud\Laravel\Events\Controller\PreIndex;
use TempestTools\Crud\Laravel\Events\Controller\PreShow;
use TempestTools\Crud\Laravel\Events\Controller\PreStore;
use TempestTools\Crud\Laravel\Events\Controller\PreUpdate;

class ControllerEventsConstants
{
    const INIT = [
        'class'=>Init::class,
        'on'=>'onInit'
    ];
    const POST_DESTROY = [
        'class'=>PostDestroy::class,
        'on'=>'onPostDestroy'
    ];
    const POST_INDEX = [
        'class'=>PostIndex::class,
        'on'=>'onPostIndex'
    ];
    const POST_SHOW = [
        'class'=>PostShow::class,
        'on'=>'onPostShow'
    ];
    const POST_STORE = [
        'class'=>PostStore::class,
        'on'=>'onPostStore'
    ];
    const POST_UPDATE = [
        'class'=>PostUpdate::class,
        'on'=>'onPostUpdate'
    ];

    const PRE_DESTROY = [
        'class'=>PreDestroy::class,
        'on'=>'onPreDestroy'
    ];


    const PRE_INDEX = [
        'class'=>PreIndex::class,
        'on'=>'onPreIndex'
    ];
    const PRE_SHOW = [
        'class'=>PreShow::class,
        'on'=>'onPreShow'
    ];
    const PRE_STORE = [
        'class'=>PreStore::class,
        'on'=>'onPreStore'
    ];
    const PRE_UPDATE = [
        'class'=>PreUpdate::class,
        'on'=>'onPreUpdate'
    ];

    /**
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