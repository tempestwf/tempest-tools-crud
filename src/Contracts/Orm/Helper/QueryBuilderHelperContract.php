<?php
namespace TempestTools\Scribe\Contracts\Orm\Helper;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Scribe\Contracts\Orm\Builder\ArrayToQueryBuilderBuilderContract;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface QueryBuilderHelperContract extends ArrayHelperContract
{


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $params
     * @param array $frontEndOptions
     * @param array $optionOverrides
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \RuntimeException
     */
    public function read(array $params = [], array $frontEndOptions = [], array $optionOverrides = []): array;


    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    public function prepareResult(QueryBuilderWrapperContract $qb, array $extra): array;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilderWrapperContract $qb, array $extra): void;

    /**
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilderWrapperContract $qb, array $extra): void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilderWrapperContract $qb, array $extra): void;

    /**
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilderWrapperContract $qb, array $extra): void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilderWrapperContract $qb, array $extra): void;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilderWrapperContract $qb, array $extra): void;

    /**
     * @param array $extra
     * @return array
     */
    public function buildCacheSettings (array $extra):array;

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilderWrapperContract $qb, array $extra): void;


    /**
     * @return \TempestTools\Scribe\Contracts\Orm\RepositoryContract
     */
    public function getRepository(): RepositoryContract;

    /**
     * @param RepositoryContract $repository
     */
    public function setRepository(RepositoryContract $repository): void;

    /**
     * @return ArrayToQueryBuilderBuilderContract
     */
    public function getArrayToQueryBuilderBuilder(): ArrayToQueryBuilderBuilderContract;

    /**
     * @param ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder
     */
    public function setArrayToQueryBuilderBuilder(ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder):void;

    /**
     * @param $value
     * @return string
     */
    public function makePlaceholderName($value):string;
}
?>