<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/2/2017
 * Time: 5:51 PM
 */

namespace TempestTools\Crud\Constants;


/**
 * Constants related to events that will be fired on repositories
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class RepositoryEventsConstants{

    /**
     * preStart event constant
     */
    const PRE_START = 'preStart';
    /**
     * preStop event constant
     */
    const PRE_STOP = 'preStop';
    /**
     * preCreateBatch event constant
     */
    const PRE_CREATE_BATCH = 'preCreateBatch';
    /**
     * preCreate event constant
     */
    const PRE_CREATE = 'preCreate';
    /**
     * validateCreate event constant
     */
    const VALIDATE_CREATE = 'validateCreate';
    /**
     * verifyCreate event constant
     */
    const VERIFY_CREATE = 'verifyCreate';
    /**
     * processResultsCreate event constant
     */
    const PROCESS_RESULTS_CREATE = 'processResultsCreate';
    /**
     * postCreate event constant
     */
    const POST_CREATE = 'postCreate';
    /**
     * postCreateBatch event constant
     */
    const POST_CREATE_BATCH = 'postCreateBatch';
    /**
     * preRead event constant
     */
    const PRE_READ = 'preRead';
    /**
     * validateRead event constant
     */
    const VALIDATE_READ = 'validateRead';
    /**
     * verifyRead event constant
     */
    const VERIFY_READ = 'verifyRead';
    /**
     * processResultsRead event constant
     */
    const PROCESS_RESULTS_READ = 'processResultsRead';
    /**
     * postRead event constant
     */
    const POST_READ = 'postRead';
    /**
     * preUpdateBatch event constant
     */
    const PRE_UPDATE_BATCH = 'preUpdateBatch';
    /**
     * preUpdate event constant
     */
    const PRE_UPDATE = 'preUpdate';
    /**
     * validateUpdate event constant
     */
    const VALIDATE_UPDATE = 'validateUpdate';
    /**
     * verifyUpdate event constant
     */
    const VERIFY_UPDATE = 'verifyUpdate';
    /**
     * processResultsUpdate event constant
     */
    const PROCESS_RESULTS_UPDATE = 'processResultsUpdate';
    /**
     * postUpdate event constant
     */
    const POST_UPDATE = 'postUpdate';
    /**
     * postUpdateBatch event constant
     */
    const POST_UPDATE_BATCH = 'postUpdateBatch';
    /**
     * preDeleteBatch event constant
     */
    const PRE_DELETE_BATCH = 'preDeleteBatch';
    /**
     * preDelete event constant
     */
    const PRE_DELETE = 'preDelete';
    /**
     * validateDelete event constant
     */
    const VALIDATE_DELETE = 'validateDelete';
    /**
     * verifyDelete event constant
     */
    const VERIFY_DELETE = 'verifyDelete';
    /**
     * processResultsDelete event constant
     */
    const PROCESS_RESULTS_DELETE = 'processResultsDelete';
    /**
     * postDelete event constant
     */
    const POST_DELETE = 'postDelete';
    /**
     * postDeleteBatch event constant
     */
    const POST_DELETE_BATCH = 'postDeleteBatch';

    /**
     * Gets info on all events
     * @return array
     */
    public static function getAll():array {
        return [
            static::PRE_START,
            static::PRE_STOP,

            static::PRE_CREATE_BATCH,
            static::PRE_CREATE,
            static::VALIDATE_CREATE,
            static::VERIFY_CREATE,
            static::PROCESS_RESULTS_CREATE,
            static::POST_CREATE,
            static::POST_CREATE_BATCH,

            static::PRE_READ,
            static::VALIDATE_READ,
            static::VERIFY_READ,
            static::PROCESS_RESULTS_READ,
            static::POST_READ,

            static::PRE_UPDATE_BATCH,
            static::PRE_UPDATE,
            static::VALIDATE_UPDATE,
            static::VERIFY_UPDATE,
            static::PROCESS_RESULTS_UPDATE,
            static::POST_UPDATE,
            static::POST_UPDATE_BATCH,

            static::PRE_DELETE_BATCH,
            static::PRE_DELETE,
            static::VALIDATE_DELETE,
            static::VERIFY_DELETE,
            static::PROCESS_RESULTS_DELETE,
            static::POST_DELETE,
            static::POST_DELETE_BATCH,
        ];
    }
}