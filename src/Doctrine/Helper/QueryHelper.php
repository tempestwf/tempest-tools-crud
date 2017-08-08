<?php
namespace TempestTools\Crud\Doctrine\Helper;


use Doctrine\ORM\Query\Expr;
use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use Doctrine\ORM\QueryBuilder;

class QueryHelper extends ArrayHelper implements \TempestTools\Crud\Contracts\QueryHelper {
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;

    const ERRORS = [
        'placeholderNoAllowed'=>[
            'message'=>'Error: You do not have access requested placeholder. placeholder = %s',
        ],
        'operatorNotAllowed'=>[
            'message'=>'Error: Operator not allowed. field = %s, operator = %s',
        ],
        'fieldBadlyFormed'=>[
            'message'=>'Error: Fields must be passed as [table alias].[field name]. field = %s',
        ],
        'orderByNotAllowed'=>[
            'message'=>'Error: Order by not allowed. field = %s, direction = %s',
        ],
        'groupByNotAllowed'=>[
            'message'=>'Error: Group by not allowed. field = %s',
        ],

    ];



    const FIELD_REGEX = '/^\w+\.\w+$/';


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilder $qb
     * @param $params
     * @param $options
     * @param $optionOverrides
     * @param $frontEndOptions
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(QueryBuilder $qb, $params, $options, $optionOverrides, $frontEndOptions)
    {
        $extra = [
            'params'=>$params,
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'frontEndOptions'=>$frontEndOptions,
        ];
        $this->buildBaseQuery($qb, $extra);
        $this->applyCachingToQuery($qb, $extra);
        $this->addPlaceholders($qb, $extra);
        $this->addFrontEndWhere($qb, $frontEndOptions);
        $this->addFrontEndOrderBys($qb, $frontEndOptions);
        $this->addFrontEndGroupBys($qb, $frontEndOptions);

    }

    /**
     * @param QueryBuilder $qb
     * @param array $frontEndOptions
     * @param bool $verify
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilder $qb, array $frontEndOptions, bool $verify = true):void
    {
        if ($verify === true) {
            $this->verifyFrontEndGroupBys($frontEndOptions);
        }
        $groupBys = $frontEndOptions['query']['groupBy'] ?? [];
        foreach ($groupBys as $key => $value) {
            $qb->groupBy($key);
        }
    }


    /**
     * @param array $frontEndOptions
     * @throws \RuntimeException
     * @internal param array $extra
     */
    public function verifyFrontEndGroupBys (array $frontEndOptions):void
    {
        $groupBys = $frontEndOptions['query']['groupBy'] ?? [];
        $permissions = $this->getArray()['permissions']['groupBy'] ?? [];
        foreach ($groupBys as $key => $value) {
            $this->verifyFieldFormat($key);

            $allowed = $this->permissiveAllowedCheck($permissions, $value);
            if ($allowed === false) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('groupByNotAllowed')['message'], $key));

            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $frontEndOptions
     * @param bool $verify
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilder $qb, array $frontEndOptions, bool $verify = true):void {
        if ($verify === true) {
            $this->verifyFrontEndOrderBys($frontEndOptions);
        }
        $orderBys = $frontEndOptions['query']['orderBy'] ?? [];
        foreach ($orderBys as $key => $value) {
            $direction = $value['direction'];
            $qb->orderBy($key, $direction);
        }
    }

    /**
     * @param $frontEndOptions
     * @throws \RuntimeException
     */
    public function verifyFrontEndOrderBys ($frontEndOptions):void
    {
        $orderBys = $frontEndOptions['query']['orderBy'] ?? [];
        $permissions = $this->getArray()['permissions']['orderBy'] ?? [];
        foreach ($orderBys as $fieldName => $value) {
            $this->verifyFieldFormat($fieldName);
            $fieldSettings = $permissions['fields'][$fieldName];
            $direction = $value['direction'];
            $allowed = $this->permissivePermissionCheck($permissions, $fieldSettings, 'directions', $direction);
            if ($allowed === false) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('orderByNotAllowed')['message'], $fieldName, $direction));
            }
        }

    }

    /**
     * @param QueryBuilder $qb
     * @param array $frontEndOptions
     * @param bool $verify
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilder $qb, array $frontEndOptions, bool $verify = true):void
    {
        if ($verify === true) {
            $this->verifyFrontEndConditions($frontEndOptions['query']['where'], $this->getArray()['permissions']['where']);
        }
        $wheres = $frontEndOptions['query']['where'] ?? [];
        foreach ($wheres as $where) {
            $type = !isset($where['type'])?'where':null;
            $type = $type === null && $where['type'] === 'and'?'andWhere':'orWhere';
            $string = $this->buildFilterFromFrontEnd($qb->expr(), $where);
            $qb->$type($string);
        }
    }


    /**
     * @param QueryBuilder $qb
     * @param array $frontEndOptions
     * @param bool $verify
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilder $qb, array $frontEndOptions, bool $verify = true):void {
        if ($verify === true) {
            $this->verifyFrontEndConditions($frontEndOptions['query']['having'], $this->getArray()['permissions']['having']);
        }
        $havings = $frontEndOptions['query']['having'] ?? [];
        foreach ($havings as $having) {
            $type = !isset($having['type'])?'having':null;
            $type = $type === null && $having['type'] === 'and'?'andHaving':'orHaving';
            $string = $this->buildFilterFromFrontEnd($qb->expr(), $having);
            $qb->$type($string);
        }
    }

    /**
     * @param Expr $expr
     * @param $condition
     * @return mixed
     */
    protected function buildFilterFromFrontEnd (Expr $expr, $condition):string
    {
        $operator = $condition['operator'];
        $fieldName = $condition['field'];
        $arguments = $condition['arguments']??[];
        if ($operator === 'andX' || $operator === 'orX') {
            $string = $this->buildFilterFromFrontEnd($expr, $condition['conditions']);
        } else {
            array_unshift($arguments, $fieldName);
            $string = $expr->$operator($arguments);
        }
        return $string;
    }

    /**
     * @param array $conditions
     * @param array $permissions
     * @throws \RuntimeException
     * @internal param string $part
     */
    public function verifyFrontEndConditions (array $conditions, array $permissions):void
    {
        /** @var array[] $condition */
        foreach ($conditions as $condition) {
            $operator = $condition['operator'];
            if ($operator === 'andX' || $operator === 'orX') {
                $this->verifyFrontEndConditions($condition['conditions'], $permissions);
            } else {
                $this->verifyFrontEndCondition($condition, $permissions);
            }
        }
    }

    /**
     * @param array $condition
     * @param array $permissions
     * @throws \RuntimeException
     */
    public function verifyFrontEndCondition (array $condition, array $permissions):void
    {
        $fieldName = $condition['field'];
        $operator = $condition['operator'];
        $this->verifyFieldFormat($fieldName);
        $fieldSettings = $permissions['fields'][$fieldName];
        $allowed = $this->permissivePermissionCheck($permissions, $fieldSettings, 'operators', $operator);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('operatorNotAllowed')['message'], $fieldName, $operator));
        }
    }

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool {
        $fieldFormatOk = preg_match(static::FIELD_REGEX, $field);
        if ($fieldFormatOk === false) {
            if ($noisy === false) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('fieldBadlyFormed')['message'], $field));
            }
            return false;
        }
        return true;
    }


    /**
     * @param array $frontEndOptions
     * @throws \RuntimeException
     * @internal param array $extra
     */
    public function verifyPlaceholders (array $frontEndOptions):void
    {
        $frontendPlaceholders = $frontEndOptions['query']['placeholders'] ?? [];
        $permissions = $this->getArray()['permissions']['placeholders'] ?? [];
        foreach ($frontendPlaceholders as $key => $value) {
            $allowed = $this->permissiveAllowedCheck($permissions, $value);
            if ($allowed === false) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('placeholderNoAllowed')['message'], $key));
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @param bool $verify
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilder $qb, array $extra, bool $verify = true)
    {
        if ($verify === true) {
            $this->verifyPlaceholders($extra['frontEndOptions']);
        }
        $frontendPlaceholders = $extra['frontEndOptions']['placeholders'] ?? [];
        $queryPlaceholders = $extra['params']['placeholders'] ?? [];
        $options = $extra['options']['placeholders'] ?? [];
        $overridePlaceholders = $extra['options']['optionOverrides'] ?? [];
        $placeholders = array_replace($queryPlaceholders, $options, $overridePlaceholders);
        $keys = array_keys($placeholders);
        $placeholders = array_replace($frontendPlaceholders, $placeholders);
        foreach ($placeholders as $key=>$value) {
            $type = $value['type'] ?? null;
            $value = $value['value'] ?? null;
            if (in_array($key, $keys, true)) {
                $type = $this->parse($type, $extra);
                $value = $this->parse($value, $extra);
            }
            $qb->setParameter($key, $value, $type);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilder $qb, array $extra) {
        $params = $extra['params'];
        $options = $extra['options'];
        $optionOverrides = $extra['optionOverrides'];
        $queryCacheDriver = $this->findSetting([$options, $optionOverrides], 'queryCacheDrive') ?? null;
        $resultCacheDriver = $this->findSetting([$options, $optionOverrides], 'resultCacheDriver') ?? null;
        $allowQueryCache = $this->findSetting([$options, $optionOverrides], 'allowQueryCache') ?? true;
        $useQueryCache = $params['useQueryCache'] ?? true;
        $useResultCache = $params['useResultCache'] ?? true;
        $timeToLive = $params['timeToLive'] ?? null;
        $cacheId = $params['cacheId'] ?? null;
        if ($allowQueryCache === true) {
            /** @noinspection NullPointerExceptionInspection */
            $qb->getQuery()->useQueryCache($this->getArrayHelper()->parse($useQueryCache, $extra));
            /** @noinspection NullPointerExceptionInspection */
            $qb->getQuery()->useResultCache($this->getArrayHelper()->parse($useResultCache, $extra), $this->getArrayHelper()->parse($timeToLive, $extra), $this->getArrayHelper()->parse($cacheId, $extra));
            if ($queryCacheDriver !== null) {
                $qb->getQuery()->setQueryCacheDriver($queryCacheDriver);
            }
            if ($resultCacheDriver !== null) {
                $qb->getQuery()->setResultCacheDriver($resultCacheDriver);
            }
        }

        //TODO: Add tagging in later version
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
                if ($value !== null) {
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
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $array
     * @param array $defaults
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processQueryPartArray (array $array, array $defaults, QueryBuilder $qb, array $extra):array {
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
        return $this->processQueryPartArray($array, $defaults, $qb, $extra);
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
        return $this->processQueryPartArray($array, $defaults, $qb, $extra);
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