<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 6/2/2017
 * Time: 5:51 PM
 */

namespace TempestTools\Crud\Constants;



class RepositoryEvents{
    const PRE_CREATE = 'preCreate';
    const VALIDATE_CREATE = 'validateCreate';
    const VERIFY_CREATE = 'verifyCreate';
    const PROCESS_RESULTS_CREATE = 'processResultsCreate';
    const POST_CREATE = 'postCreate';
    const POST_COMMIT_CREATE = 'postCommitCreate';

    const PRE_READ = 'preRead';
    const VALIDATE_READ = 'validateRead';
    const VERIFY_READ = 'verifyRead';
    const PROCESS_RESULTS_READ = 'processResultsRead';
    const POST_READ = 'postRead';
    const POST_COMMIT_READ = 'postCommitRead';

    const PRE_UPDATE = 'preUpdate';
    const VALIDATE_UPDATE = 'validateUpdate';
    const VERIFY_UPDATE = 'verifyUpdate';
    const PROCESS_RESULTS_UPDATE = 'processResultsUpdate';
    const POST_UPDATE = 'postUpdate';
    const POST_COMMIT_UPDATE = 'postCommitUpdate';

    const PRE_DELETE = 'preDelete';
    const VALIDATE_DELETE = 'validateDelete';
    const VERIFY_DELETE = 'verifyDelete';
    const PROCESS_RESULTS_DELETE = 'processResultsDelete';
    const POST_DELETE = 'postDelete';
    const POST_COMMIT_DELETE = 'postCommitDelete';

    /**
     * @return array
     */
    static public function getAll():array {
        return [
            static::PRE_CREATE,
            static::VALIDATE_CREATE,
            static::VERIFY_CREATE,
            static::PROCESS_RESULTS_CREATE,
            static::POST_CREATE,
            static::POST_COMMIT_CREATE,

            static::PRE_READ,
            static::VALIDATE_READ,
            static::VERIFY_READ,
            static::PROCESS_RESULTS_READ,
            static::POST_READ,
            static::POST_COMMIT_READ,

            static::PRE_UPDATE,
            static::VALIDATE_UPDATE,
            static::VERIFY_UPDATE,
            static::PROCESS_RESULTS_UPDATE,
            static::POST_UPDATE,
            static::POST_COMMIT_UPDATE,

            static::PRE_DELETE,
            static::VALIDATE_DELETE,
            static::VERIFY_DELETE,
            static::PROCESS_RESULTS_DELETE,
            static::POST_DELETE,
            static::POST_COMMIT_DELETE,
        ];
    }
}