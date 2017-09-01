<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/23/2017
 * Time: 2:27 PM
 */

namespace TempestTools\Crud\Contracts\Orm\Builder;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;

interface ArrayToQueryBuilderBuilderContract
{
    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $key
     * @param array $settings
     * @param ArrayHelperContract $arrayHelper
     * @param $closure
     * @param array $extra
     * @internal param EntityContract $entity
     * @internal param \Closure $fieldSetting
     * @return array
     */
    public function closure(string $key=null, $settings, ArrayHelperContract $arrayHelper, $closure, array $extra): array;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string $key
     * @param array $settings
     * @param ArrayHelperContract $arrayHelper
     * @param $closure
     * @param array $extra
     * @internal param EntityContract $entity
     * @internal param mixed $fieldSetting
     * @return array
     */
    public function mutate(string $key=null, $settings, ArrayHelperContract $arrayHelper, $closure, array $extra): array;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function select (array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function from (array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function leftJoin(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function innerJoin(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function where(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function having(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function orderBy(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     */
    public function groupBy(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void;
}