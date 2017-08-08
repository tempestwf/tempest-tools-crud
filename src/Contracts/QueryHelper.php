<?php
namespace TempestTools\Crud\Contracts;

use TempestTools\Common\Contracts\ArrayHelper;
use Doctrine\ORM\QueryBuilder;

interface QueryHelper extends ArrayHelper {
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilder $qb
     * @param $params
     * @param $options
     * @param $optionOverrides
     * @param $frontEndOptions
     */
    public function read(QueryBuilder $qb, $params, $options, $optionOverrides, $frontEndOptions);

    public function buildBaseQuery(QueryBuilder $qb, array $extra):void;


}
?>