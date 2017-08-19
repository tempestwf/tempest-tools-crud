<?php
namespace TempestTools\Crud\Contracts;

use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;

interface QueryBuilderHelperContract extends ArrayHelperContract {

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $params
     * @param array $frontEndOptions
     * @param array $optionOverrides
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \RuntimeException
     */
    public function read (array $params=[], array $frontEndOptions=[], array $optionOverrides = []):array;


    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    public function prepareResult (QueryBuilderWrapperContract $qb, array $extra):array;
    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilderWrapperContract $qb, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilderWrapperContract $qb, array $extra):void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilderWrapperContract $qb, array $extra):void;

}
?>