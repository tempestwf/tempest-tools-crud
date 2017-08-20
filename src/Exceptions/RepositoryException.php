<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions;


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



