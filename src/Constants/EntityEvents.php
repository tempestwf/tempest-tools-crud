<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/2/2017
 * Time: 5:51 PM
 */

namespace TempestTools\Crud\Constants;



class EntityEvents{
    const PRE_SET_FIELD = 'preSetField';
    const PRE_PROCESS_ASSOCIATION_PARAMS = 'preProcessAssociationParams';
    const PRE_PERSIST = 'prePersist';
    const POST_PERSIST = 'postPersist';

    /**
     * @return array
     */
    static public function getAll():array {
        return [
            static::PRE_SET_FIELD,
            static::PRE_PROCESS_ASSOCIATION_PARAMS,
            static::PRE_PERSIST,
            static::POST_PERSIST
        ];
    }
}