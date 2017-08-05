<?php
namespace TempestTools\Crud\Doctrine\Helper;


use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use Doctrine\ORM\QueryBuilder;

class QueryHelper extends ArrayHelper implements \TempestTools\Crud\Contracts\QueryHelper {
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilder $qb
     * @param $params
     * @param $options
     * @param $optionOverrides
     * @param $frontEndOption
     */
    public function read(QueryBuilder $qb, $params, $options, $optionOverrides, $frontEndOption) {
        $extra = [
            'params'=>$params,
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'frontEndOption'=>$frontEndOption,
        ];
        $this->buildBaseQuery($qb, $extra);

    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilder $qb, array $extra):void {
        $config = $this->getArray()['read'];
        /** @var array $config */
        foreach ($config as $queryPart => $entries) {
            /**
             * @var array $entries
             * @var string $key
             * @var  array $value
             */
            foreach ($entries as $key => $value) {
                switch ($queryPart) {
                    case 'select':
                        $value = $this->processQueryPart($value, $qb, $extra);
                        $qb->addSelect($value);
                        break;
                    case 'leftJoin':
                        $value = $this->processJoinParams($value, $qb, $extra);
                        $qb->leftJoin($value['join'], $value['alias'], $value['conditionType'], $value['condition'], $value['indexBy']);
                        break;
                    case 'innerJoin':
                        $value = $this->processJoinParams($value, $qb, $extra);
                        $qb->innerJoin($value['join'], $value['alias'], $value['conditionType'], $value['condition'], $value['indexBy']);
                        break;
                    case 'where':
                        $where = $this->processQueryPart($value['value'], $qb, $extra);
                        if (isset($value['type'])) {
                            if ($value['type'] === 'and') {
                                $qb->andWhere($where);
                            } else if ($value['type'] === 'or') {
                                $qb->orWhere($where);
                            }
                        } else {
                            $qb->where($where);
                        }
                        break;
                    case 'having':
                        $having = $this->processQueryPart($value['value'], $qb, $extra);
                        if (isset($value['type'])) {
                            if ($value['type'] === 'and') {
                                $qb->andHaving($having);
                            } else if ($value['type'] === 'or') {
                                $qb->orHaving($having);
                            }
                        } else {
                            $qb->having($having);
                        }
                        break;
                    case 'orderBy':
                        $value = $this->processOrderParams($value, $qb, $extra);
                        $qb->addOrderBy($value['sort'], $value['order']);
                        break;
                    case 'groupBy':
                        $value = $this->processQueryPart($value, $qb, $extra);
                        $qb->groupBy($value);
                        break;
                }
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $array
     * @param array $defaults
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processArray (array $array, array $defaults, QueryBuilder $qb, array $extra):array {
        foreach ($array as $key => $value) {
            $array[$key] = $this->processQueryPart($value, $qb, $extra);
        }
        return array_replace($defaults, $array);
    }

    /**
     * @param array $array
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processJoinParams(array $array, QueryBuilder $qb, array $extra):array {
        $defaults = [
            'join'=>null,
            'alias'=>null,
            'conditionType'=>null,
            'condition'=>null,
            'indexBy'=>null
        ];
        return $this->processArray($array, $defaults, $qb, $extra);
    }

    /**
     * @param array $array
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processOrderParams(array $array, QueryBuilder $qb, array $extra):array {
        $defaults = [
            'sort'=>null,
            'order'=>null
        ];
        return $this->processArray($array, $defaults, $qb, $extra);
    }

    /**
     * @param $value
     * @param QueryBuilder $qb
     * @param array $extra
     * @return string
     */
    protected function processQueryPart($value, QueryBuilder $qb, array $extra):string {
        if (is_array($value)) {
            /** @var array[] $value */
            foreach ($value['arguments'] as &$argument) {
                if (is_array($argument)) {
                    $argument = $this->processQueryPart($argument, $qb, $extra);
                } else {
                    /** @noinspection NullPointerExceptionInspection */
                    $argument = $this->getArrayHelper()->parse($argument, $extra);
                }
            }
            return call_user_func_array ([$qb->expr(), $value['expr']], $value['arguments']);
        }

        /** @noinspection NullPointerExceptionInspection */
        return $this->getArrayHelper()->parse($value, $extra);
    }
}
?>