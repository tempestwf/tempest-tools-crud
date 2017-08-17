<?php
namespace TempestTools\Crud\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityRepository;
use TempestTools\Crud\Contracts\RepositoryContract;

abstract class RepositoryAbstract extends EntityRepository implements EventSubscriber, RepositoryContract
{

    use  /** @noinspection TraitsPropertiesConflictsInspection */ RepositoryTrait;

    /**
     * ERRORS
     */
    const ERRORS = [
        'noArrayHelper'=>[
            'message'=>'Error: No array helper set on repository.',
        ],
        'entityToBindNotFound'=>[
            'message'=>'Error: Entity to bind not found.',
        ],
        'moreRowsRequestedThanBatchMax'=>[
            'message'=>'Error: More rows requested than batch max allows. count = %s, max = %s',
        ],
        'wrongTypeOfRepo'=>[
            'message'=>'Error: Wrong type of repo used with chaining.',
        ],
        'moreQueryParamsThanMax'=>[
            'message'=>'Error: More query params than passed than permitted. count = %s, max = %s'
        ],
        'queryTypeNotRecognized'=>[
            'message'=>'Error: Query type from configuration not recognized. query type = %s'
        ],
    ];


}
?>