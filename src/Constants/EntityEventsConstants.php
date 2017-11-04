<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/2/2017
 * Time: 5:51 PM
 */

namespace TempestTools\Scribe\Constants;

/**
 * Constants related to events that will be fired on entities
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class EntityEventsConstants{
    /**
     * preSetField event constant
     */
    const PRE_SET_FIELD = 'preSetField';
    /**
     * preProcessAssociationParams event constant
     */
    const PRE_PROCESS_ASSOCIATION_PARAMS = 'preProcessAssociationParams';
    /**
     * prePersist event constant
     */
    const PRE_PERSIST = 'prePersist';
    /**
     * postPersist event constant
     */
    const POST_PERSIST = 'postPersist';
    /**
     * preToArray event constant
     */
    const PRE_TO_ARRAY = 'preToArray';
    /**
     * postToArray event constant
     */
    const POST_TO_ARRAY = 'postToArray';


    /**
     * Gets info on all events
     * @return array
     */
    public static function getAll():array {
        return [
            static::PRE_SET_FIELD,
            static::PRE_PROCESS_ASSOCIATION_PARAMS,
            static::PRE_PERSIST,
            static::POST_PERSIST,
            static::PRE_TO_ARRAY,
            static::POST_TO_ARRAY,
        ];
    }
}