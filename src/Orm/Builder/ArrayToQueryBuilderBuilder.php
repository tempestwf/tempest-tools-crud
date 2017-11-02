<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 4:53 PM
 */

namespace TempestTools\Crud\Orm\Builder;

use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToQueryBuilderBuilderContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException;
use TempestTools\Crud\Orm\Utility\BadBuilderCallTrait;

/**
 * A builder that takes data store on an array, verifies it and modifies it as needed. This is used when processing data that will be used in regards to an a query being built.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class ArrayToQueryBuilderBuilder implements ArrayToQueryBuilderBuilderContract
{
    use BadBuilderCallTrait;

    /** @noinspection MoreThanThreeArgumentsInspection
     * Verifies that the parameters passed to this method to be used as part of a requested filter are valid by passing them through a closure stored in the config
     * @param string $key
     * @param array $settings
     * @param ArrayHelperContract $arrayHelper
     * @param $closure
     * @param array $extra
     * @internal param EntityContract $entity
     * @internal param \Closure $fieldSetting
     * @return array
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
     */
    public function closure(string $key=null, $settings, ArrayHelperContract $arrayHelper, $closure, array $extra):array
    {
        $extra['key'] = $key;
        $extra['settings'] = $settings;
        if ($arrayHelper->parse($closure, $extra) === false) {
            throw QueryBuilderHelperException::closureFails();
        }
        return [$key, $settings];
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * Modifies the parameters passed to this method to be used as part of a requested filter using a closure stored in the config
     * @param string $key
     * @param array $settings
     * @param ArrayHelperContract $arrayHelper
     * @param $closure
     * @param array $extra
     * @internal param EntityContract $entity
     * @internal param mixed $fieldSetting
     * @return array
     * @throws \RuntimeException
     */
    public function mutate (string $key=null, $settings, ArrayHelperContract $arrayHelper, $closure, array $extra):array
    {
        $extra['key'] = $key;
        $extra['settings'] = $settings;
        return $arrayHelper->parse($closure, $extra);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * Builds the select part of a query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function select (array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        $firstSelect = true;
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                /** @var string $value */
                /** @noinspection NullPointerExceptionInspection */
                $value = $arrayHelper->parse($value, $extra);
                if ($firstSelect === true) {
                    $qb->select($value, false);
                    $firstSelect = false;
                } else {
                    $qb->select($value);
                }
            }
        }
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the from part of a query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function from (array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                $value = $this->processFrom($value, $qb, $extra, $arrayHelper);
                if ($value['append'] === false) {
                    $qb->from($value['className'], $value['alias'], $value['indexBy']);
                } else {
                    $qb->from($value['className'], $value['alias'], $value['indexBy'], true);
                }
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the left join part of a query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function leftJoin(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                $value = $this->processJoinParams($value, $qb, $extra, $arrayHelper);
                $qb->leftJoin($value['join'], $value['alias'], $value['conditionType'], $value['condition'], $value['indexBy']);
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the inner join part of a query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function innerJoin(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                $value = $this->processJoinParams($value, $qb, $extra, $arrayHelper);
                $qb->innerJoin($value['join'], $value['alias'], $value['conditionType'], $value['condition'], $value['indexBy']);
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the where part of the query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function where(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                /** @noinspection NullPointerExceptionInspection */
                $where = is_array($value['value']) && isset($value['value']['arguments'])?$this->processQueryPartExpr($value['value'], $qb, $extra, $arrayHelper):$arrayHelper->parse($value['value'], $extra);
                if (isset($value['type'])) {
                    $qb->where($value['type'], $where);
                } else {
                    $qb->where(null, $where, false);
                }
            }
        }
    }


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the having part of the query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function having(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                /** @noinspection NullPointerExceptionInspection */
                $where = is_array($value['value']) && isset($value['value']['arguments']) ? $this->processQueryPartExpr($value['value'], $qb, $extra, $arrayHelper) : $arrayHelper->parse($value['value'], $extra);
                if (isset($value['type'])) {
                    $qb->having($value['type'], $where);
                } else {
                    $qb->having(null, $where, false);
                }
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the order by part of the query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function orderBy(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                $value = $this->processOrderParams($value, $qb, $extra, $arrayHelper);
                $qb->orderBy($value['sort'], $value['order']);
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the group by part of the query
     * @param array $entries
     * @param QueryBuilderWrapperContract $qb
     * @param ArrayHelperContract $arrayHelper
     * @param array $extra
     * @throws \RuntimeException
     */
    public function groupBy(array $entries, QueryBuilderWrapperContract $qb, ArrayHelperContract $arrayHelper, array $extra):void
    {
        foreach ($entries as $key => $value) {
            if ($value !== null) {
                /** @noinspection NullPointerExceptionInspection */
                $value = $arrayHelper->parse($value, $extra);
                /** @var string $value */
                $qb->groupBy($value);
            }
        }
    }



    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * Prepares data to build the from part of the query
     * @param array $array
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @param ArrayHelperContract $arrayHelper
     * @return array
     * @throws \RuntimeException
     */
    protected function processFrom(array $array, QueryBuilderWrapperContract $qb, array $extra, ArrayHelperContract $arrayHelper):array
    {
        $defaults = [
            'className'=>null,
            'alias'=>null,
            'indexBy'=>null,
            'append'=>false
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra, $arrayHelper);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Prepares data for a where or having part of the query
     * @param array $array
     * @param array $defaults
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @param ArrayHelperContract $arrayHelper
     * @return array
     * @throws \RuntimeException
     */
    protected function processQueryPartArray (array $array, array $defaults, QueryBuilderWrapperContract $qb, array $extra, ArrayHelperContract $arrayHelper):array
    {
        foreach ($array as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $array[$key] = is_array($value) && isset($value['arguments'])?$this->processQueryPartExpr($value, $qb, $extra, $arrayHelper):$array[$key] = $arrayHelper->parse($value, $extra);
        }
        return array_replace($defaults, $array);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Creates a query part by calling an expression
     * @param $value
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     * @param ArrayHelperContract $arrayHelper
     * @return null|string
     * @throws \RuntimeException
     */
    protected function processQueryPartExpr($value, QueryBuilderWrapperContract $qb, array $extra, ArrayHelperContract $arrayHelper):?string
    {
        /** @var array[] $value */
        foreach ($value['arguments'] as &$argument) {
            if (is_array($argument) && isset($argument['expr'])) {
                $argument = $this->processQueryPartExpr($argument, $qb, $extra, $arrayHelper);
            } else {
                $argument = $arrayHelper->parse($argument, $extra);
            }
        }
        unset($argument);
        /** @var string $expr */
        $expr = $value['expr'];
        return $qb->useExpression($expr, $value['arguments']);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Prepares data for a join part of the query
     * @param array $array
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     * @param ArrayHelperContract $arrayHelper
     * @return array
     * @throws \RuntimeException
     */
    protected function processJoinParams(array $array, QueryBuilderWrapperContract $qb, array $extra, ArrayHelperContract $arrayHelper):array
    {
        $defaults = [
            'join'=>null,
            'alias'=>null,
            'conditionType'=>null,
            'condition'=>null,
            'indexBy'=>null
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra, $arrayHelper);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Prepares data for a order by part of the query
     * @param array $array
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @param ArrayHelperContract $arrayHelper
     * @return array
     * @throws \RuntimeException
     */
    protected function processOrderParams(array $array, QueryBuilderWrapperContract $qb, array $extra, ArrayHelperContract $arrayHelper):array
    {
        $defaults = [
            'sort'=>null,
            'order'=>null
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra, $arrayHelper);
    }


}