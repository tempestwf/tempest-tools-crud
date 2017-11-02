<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions\Orm;

/**
 * Exception for errors that can happen on a repository.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class RepositoryException extends \RunTimeException
{
    /**
     * @param string $queryType
     * @return RepositoryException
     */
    public static function queryTypeNotRecognized (string $queryType): RepositoryException
    {
        return new self (sprintf('Error: Query type from configuration not recognized. query type = %s', $queryType));
    }


}



