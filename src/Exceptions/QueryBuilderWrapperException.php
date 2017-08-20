<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/19/2017
 * Time: 6:52 PM
 */

namespace TempestTools\Crud\Exceptions;


class QueryBuilderWrapperException extends \RunTimeException
{
    /**
     * @return QueryBuilderWrapperException
     */
    public static function countRequiresPaginator (): QueryBuilderWrapperException
    {
        return new self ('Error: Getting a count requires use of the paginator');
    }


    /**
     * @param string $arg
     * @return QueryBuilderWrapperException
     */
    public static function parameterTypeNotSupported (string $arg): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Parameter type is not supported. type = %s', $arg));
    }

    /**
     * @param string $arg
     * @return QueryBuilderWrapperException
     */
    public static function fieldBadlyFormed (string $arg): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Fields must be passed as [table alias].[field name]. field = %s', $arg));
    }

    /**
     * @param string $arg
     * @return QueryBuilderWrapperException
     */
    public static function directionNotAllow (string $arg): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Order by direction not allowed. direction = %s', $arg));
    }

    /**
     * @param string $arg
     * @return QueryBuilderWrapperException
     */
    public static function operatorNotSafe (string $arg): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Requested operator is not safe to use. operator = %s', $arg));
    }

    /**
     * @return QueryBuilderWrapperException
     */
    public static function paginationNotCompatible (): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Pagination is not compatible with this type of query'));
    }

    /**
     * @return QueryBuilderWrapperException
     */
    public static function hydrationNotCompatible (): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Hydration is not compatible with this type of query'));
    }

    /**
     * @return QueryBuilderWrapperException
     */
    public static function cacheNotCompatible (): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: Caching is not compatible with this type of query'));
    }

    /**
     * @return QueryBuilderWrapperException
     */
    public static function indexByNotCompatible (): QueryBuilderWrapperException
    {
        return new self (sprintf('Error: indexBy is not compatible with this type of query'));
    }


}



