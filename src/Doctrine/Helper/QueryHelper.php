<?php
namespace TempestTools\Crud\Doctrine\Helper;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use Doctrine\ORM\QueryBuilder;

class QueryHelper extends ArrayHelper implements \TempestTools\Crud\Contracts\QueryHelper
{
    use TTConfigTrait, ErrorConstantsTrait, ArrayHelperTrait;

    /**
     * ERRORS
     */
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
        'maxLimitHit'=>[
            'message'=>'Error: Requested limit greater than max. limit = %s, max = %s',
        ],
        'operatorNotSafe'=>[
            'message'=>'Error: Requested operator is not safe to use. operator = %s',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass while building query.',
        ],
        'readRequestNotAllowed' => [
            'message' => 'Error: Read request not allowed.',
        ],
        'directionNotAllow' => [
            'message' => 'Error: Order by direction not allowed. direction = %s',
        ],

    ];



    /**
     * FIELD_REGEX
     */
    const FIELD_REGEX = '/^\w+\.\w+$/';

    /**
     * DEFAULT_LIMIT
     */
    const DEFAULT_LIMIT = 25;

    /**
     * DEFAULT_MAX_LIMIT
     */
    const DEFAULT_MAX_LIMIT = 100;

    /**
     * DEFAULT_OFFSET
     */
    const DEFAULT_OFFSET = 0;

    /**
     * DEFAULT_RETURN_COUNT
     */
    const DEFAULT_RETURN_COUNT = true;

    /**
     * DEFAULT_FETCH_JOIN
     */
    const DEFAULT_FETCH_JOIN = true;

    /**
     * SAFE_OPERATORS
     */
    const SAFE_OPERATORS = ['andX', 'orX', 'eq', 'neq', 'lt', 'lte', 'gt', 'gte', 'in', 'notIn', 'isNull', 'isNotNull', 'like', 'notLike', 'between' ];

    /**
     * ORDER_BY_DIRECTIONS
     */
    const ORDER_BY_DIRECTIONS = ['ASC', 'DESC'];

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @param array $frontEndOptions
     * @param array $options
     * @param array $optionOverrides
     * @return array
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(QueryBuilder $qb, array $params, array $frontEndOptions, array $options, array $optionOverrides):array
    {
        $extra = [
            'params'=>$params,
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'frontEndOptions'=>$frontEndOptions,
            'qb'=>$qb,
            'helper'=>$this
        ];
        $this->verifyAllowed($extra);
        $this->buildBaseQuery($qb, $extra);
        $this->applyCachingToQuery($qb, $extra);
        $this->addPlaceholders($qb, $extra);
        $this->addFrontEndWhere($qb, $extra);
        $this->addFrontEndHaving($qb, $extra);
        $this->addFrontEndOrderBys($qb, $extra);
        $this->addFrontEndGroupBys($qb, $extra);
        $this->addLimitAndOffset($qb, $extra);
        return $this->prepareResult($qb, $extra);
    }

    /**
     * @param array $extra
     * @throws \RuntimeException
     */
    public function verifyAllowed(array $extra):void
    {
        $config = $this->getArray()['permissions'] ?? [];
        $allowed = $config['allowed']?? true;
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('readRequestNotAllowed')['message']));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    public function prepareResult (QueryBuilder $qb, array $extra):array
    {
        $options = $extra['options']??[];
        $optionOverrides = $extra['optionOverrides']??[];
        $frontEndOptions = $extra['frontEndOptions']??[];
        $hydrationType = $this->findSetting([$options, $optionOverrides], 'hydrationType');
        $paginate = $this->findSetting([$options, $optionOverrides], 'paginate');
        $returnCount = $frontEndOptions['options']['returnCount'] ?? static::DEFAULT_RETURN_COUNT;
        $hydrate = $this->findSetting([$options, $optionOverrides], 'hydrate');
        $fetchJoin = $this->getArray()['read']['fetchJoin'] ?? static::DEFAULT_FETCH_JOIN;
        $count = null;
        if ($hydrate !== true) {
            return ['qb'=>$qb];
        }

        if ($paginate === true) {
            $paginator = new Paginator($qb->getQuery());
            $paginator->getQuery()->setHydrationMode($hydrationType);
            $count = $returnCount?count($paginator, $fetchJoin):null;
            $result = $paginator->getIterator()->getArrayCopy();
        } else {
            $qb->getQuery()->setHydrationMode($hydrationType);
            $result = $qb->getQuery()->getResult();
        }
        return ['count'=>$count, 'result'=>$result];
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilder $qb, array $extra):void
    {
        $frontEndOptions = $extra['frontEndOptions'];

        $limit = isset($frontEndOptions['limit']) ? (int)$frontEndOptions['limit']:static::DEFAULT_LIMIT;
        $offset = isset($frontEndOptions['offset']) ? (int)$frontEndOptions['offset']:static::DEFAULT_OFFSET;

        $this->verifyLimitAndOffset($limit, $extra);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
    }


    /**
     * @param int $limit
     * @param array $extra
     * @internal param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyLimitAndOffset (int $limit, array $extra):void
    {
        $maxLimit = $this->getArray()['permissions']['maxLimit'] ?? static::DEFAULT_MAX_LIMIT;
        /** @noinspection NullPointerExceptionInspection */
        $maxLimit = (int)$this->getArrayHelper()->parse($maxLimit, $extra);
        if ($limit > $maxLimit) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('maxLimitHit')['message'], $limit, $maxLimit));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilder $qb, array $extra):void
    {
        $params = $extra['params']??[];
        $groupBys = $params['query']['groupBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['groupBy'] ?? [], $extra) ?? [];
        foreach ($groupBys as $key => $value) {
            $fastMode = $this->highLowSettingCheck($permissions, $permissions['fields'][$key] ?? [], 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndGroupBys($key, $value, $permissions, $extra);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $value] = $this->closureMutate($key, $value, $extra);
            }
            $qb->groupBy($key);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param string $key
     * @param string $value
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyFrontEndGroupBys (string $key, string $value, array $permissions, array $extra):void
    {
        $this->closurePermission($permissions, $extra);
        $this->verifyFieldFormat($key);

        $allowed = $this->permissiveAllowedCheck($permissions, $permissions['fields'][$key] ?? []);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('groupByNotAllowed')['message'], $key));
        }
    }


    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilder $qb, array $extra):void
    {
        $params = $extra['params'];
        $orderBys = $params['query']['orderBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['orderBy'] ?? [], $extra) ?? [];
        foreach ($orderBys as $key => $value) {
            $fastMode = $this->highLowSettingCheck($permissions, $permissions['fields'][$key] ?? [], 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndOrderBys($key, $value, $permissions, $extra);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $value] = $this->closureMutate($key, $value, $extra);
            }
            $qb->orderBy($key, $value);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $key
     * @param string $value
     * @param array $permissions
     * @param $extra
     * @throws \RuntimeException
     */
    public function verifyFrontEndOrderBys (string $key, string $value, array $permissions, $extra):void
    {
        $this->closurePermission($permissions, $extra);
        $this->verifyFieldFormat($key);
        $fieldSettings = $permissions['fields'][$key]??[];
        $this->verifyDirectionFormat($value);
        $allowed = $this->permissivePermissionCheck($permissions, $fieldSettings, 'directions', $value);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('orderByNotAllowed')['message'], $key, $value));
        }

    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilder $qb, array $extra):void
    {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['where']??[], $extra) ?? [];
        $wheres = $params['query']['where'] ?? [];
        foreach ($wheres as $where) {
            $type = !isset($where['type'])?'where':null;
            $type = $type === null || $type === 'and'?'andWhere':'orWhere';
            $string = $this->buildFilterFromFrontEnd($qb->expr(), $where, $permissions, $extra);
            $qb->$type($string);
        }
    }


    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilder $qb, array $extra):void {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['having'] ?? [], $extra);
        $havings = $params['query']['having'] ?? [];
        foreach ($havings as $having) {
            $type = !isset($having['type'])?'having':null;
            $type = $type === null || $type === 'and'?'andHaving':'orHaving';
            $string = $this->buildFilterFromFrontEnd($qb->expr(), $having, $permissions, $extra);
            $qb->$type($string);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Expr $expr
     * @param array $condition
     * @param array $permissions
     * @param array $extra
     * @return string
     * @throws \RuntimeException
     */
    protected function buildFilterFromFrontEnd (Expr $expr, array $condition, array $permissions, array $extra):string
    {
        $operator = $condition['operator'];
        $fieldName = $condition['field'];
        $arguments = $condition['arguments']??[];
        $conditions = $condition['conditions']??[];
        $fieldSettings = $permissions['fields'][$fieldName] ?? [];

        if ($operator === 'andX' || $operator === 'orX') {
            $string = $this->buildFilterFromFrontEnd($expr, $conditions, $permissions, $extra);
        } else {
            $arguments = $this->argumentsToPlaceholders($arguments, $extra);
            $fastMode = $this->highLowSettingCheck($permissions, $fieldSettings, 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndCondition($condition, $permissions);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $where] = $this->closureMutate(null, $condition, $extra);
            }
            array_unshift($arguments, $fieldName);
            $string = $expr->$operator($arguments);
        }
        return $string;
    }

    /**
     * @param array $arguments
     * @param array $extra
     * @return array
     */
    protected function argumentsToPlaceholders(array $arguments, array $extra):array
    {
        /** @var QueryBuilder $qb */
        $qb = $extra['qb'];
        $result = [];
        foreach ($arguments as $argument) {
            $placeholderName = uniqid('', true);
            $result[] = ':' . $placeholderName;
            $qb->setParameter($placeholderName, $argument);
        }
        return $result;
    }

    /**
     * @param array $condition
     * @param array $permissions
     * @throws \RuntimeException
     */
    public function verifyFrontEndCondition (array $condition, array $permissions):void
    {
        $extra = ['condition'=>$condition, 'permissions'=>$permissions];
        $fieldName = $condition['field'];
        $operator = $condition['operator'];
        $this->verifyFieldFormat($fieldName);
        $fieldSettings = $permissions['fields'][$fieldName] ?? [];
        $this->closurePermission($fieldSettings, $extra);
        /** @noinspection NullPointerExceptionInspection */
        $fieldSettings = $this->getArrayHelper()->parse($fieldSettings, $extra);
        $allowed = $this->permissivePermissionCheck($permissions, $fieldSettings, 'operators', $operator);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('operatorNotAllowed')['message'], $fieldName, $operator));
        }
    }


    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilder $qb, array $extra):void
    {
        $queryPlaceholders = $this->getArray()['read']['placeholders'] ?? [];
        $frontEndPlaceholders = $extra['params']['settings']['placeholders'] ?? [];
        $options = $extra['options']['placeholders'] ?? [];
        $overridePlaceholders = $extra['optionOverrides']['placeholders'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['placeholders'] ?? [], $extra);
        $placeholders = array_replace($queryPlaceholders, $options, $overridePlaceholders);
        $keys = array_keys($placeholders);
        $placeholders = array_replace($frontEndPlaceholders, $placeholders);
        foreach ($placeholders as $key=>$value) {
            $fastMode = $this->highLowSettingCheck($permissions, $permissions['fields'][$key] ?? [], 'fastMode');
            if ($fastMode !== true) {
                $this->verifyPlaceholders($key, $value, $permissions, $extra);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $having] = $this->closureMutate($key, $value, $extra);
            }
            $type = $value['type'] ?? null;
            $value = $value['value'] ?? null;
            if (in_array($key, $keys, true)) {
                /** @noinspection NullPointerExceptionInspection */
                $type = $this->getArrayHelper()->parse($type, $extra);
                /** @noinspection NullPointerExceptionInspection */
                $value = $this->getArrayHelper()->parse($value, $extra);
            }
            $qb->setParameter($key, $value, $type);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $key
     * @param array $value
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    public function verifyPlaceholders (string $key, array $value, array $permissions , array $extra):void
    {
        $this->closurePermission($permissions, $extra);
        $allowed = $this->permissiveAllowedCheck($permissions, $value);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('placeholderNoAllowed')['message'], $key));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilder $qb, array $extra):void
    {
        $params = $extra['params'];
        $options = $extra['options'];
        $optionOverrides = $extra['optionOverrides'];
        $queryCacheDriver = $this->findSetting([$options, $optionOverrides], 'queryCacheDrive') ?? null;
        $resultCacheDriver = $this->findSetting([$options, $optionOverrides], 'resultCacheDriver') ?? null;
        $allowQueryCache = $this->findSetting([$options, $optionOverrides], 'allowQueryCache') ?? true;
        $useQueryCache = $params['settings']['useQueryCache'] ?? true;
        $useResultCache = $params['settings']['useResultCache'] ?? false;
        $timeToLive = $params['settings']['timeToLive'] ?? null;
        $cacheId = $params['settings']['cacheId'] ?? null;
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
    public function buildBaseQuery(QueryBuilder $qb, array $extra):void
    {
        $config = $this->getArray()['read'] ?? [];
        $firstSelect = true;
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
                            /** @noinspection NullPointerExceptionInspection */
                            $value = $this->getArrayHelper()->parse($value, $extra);
                            if ($firstSelect === true) {
                                $qb->select($value);
                                $firstSelect = false;
                            } else {
                                $qb->addSelect($value);
                            }
                            break;
                        case 'from':
                            $value = $this->processFrom($value, $qb, $extra);
                            if ($value['append'] === false) {
                                /** @noinspection PhpParamsInspection */
                                $qb->add('from', new Expr\From($value['className'], $value['alias'], $value['indexBy']));
                            } else {
                                $qb->from($value['className'], $value['alias'], $value['indexBy']);
                            }

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
                            /** @noinspection NullPointerExceptionInspection */
                            $where = is_array($value['value']) && isset($value['value']['arguments'])?$this->processQueryPartExpr($value['value'], $qb, $extra):$this->getArrayHelper()->parse($value['value'], $extra);
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
                            /** @noinspection NullPointerExceptionInspection */
                            $having = is_array($value['value']) && isset($value['value']['arguments'])?$this->processQueryPartExpr($value['value'], $qb, $extra):$this->getArrayHelper()->parse($value['value'], $extra);
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
                            /** @noinspection NullPointerExceptionInspection */
                            $value = $this->getArrayHelper()->parse($value, $extra);
                            /** @var string $value */
                            $qb->addGroupBy($value);
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
    protected function processQueryPartArray (array $array, array $defaults, QueryBuilder $qb, array $extra):array
    {
        foreach ($array as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $array[$key] = is_array($value) && isset($value['arguments'])?$this->processQueryPartExpr($value, $qb, $extra):$array[$key] = $this->getArrayHelper()->parse($value, $extra);
        }
        return array_replace($defaults, $array);
    }

    /**
     * @param array $array
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processJoinParams(array $array, QueryBuilder $qb, array $extra):array
    {
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
    protected function processOrderParams(array $array, QueryBuilder $qb, array $extra):array
    {
        $defaults = [
            'sort'=>null,
            'order'=>null
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra);
    }

    /**
     * @param array $array
     * @param QueryBuilder $qb
     * @param array $extra
     * @return array
     */
    protected function processFrom(array $array, QueryBuilder $qb, array $extra):array
    {
        $defaults = [
            'className'=>null,
            'alias'=>null,
            'indexBy'=>null,
            'append'=>false
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra);
    }


    /**
     * @param $value
     * @param QueryBuilder $qb
     * @param array $extra
     * @return string|null
     */
    protected function processQueryPartExpr($value, QueryBuilder $qb, array $extra):?string
    {
        /** @var array[] $value */
        foreach ($value['arguments'] as &$argument) {
            if (is_array($argument) && isset($argument['expr'])) {
                $argument = $this->processQueryPartExpr($argument, $qb, $extra);
            } else {
                /** @noinspection NullPointerExceptionInspection */
                $argument = $this->getArrayHelper()->parse($argument, $extra);
            }
        }
        return call_user_func_array ([$qb->expr(), $value['expr']], $value['arguments']);
    }

    /**
     * @param $key
     * @param array $settings
     * @param array $extra
     * @return array
     */
    public function closureMutate (string $key=null, array $settings=null, array $extra):array
    {
        $extra['key'] = $key;
        $extra['settings'] = $settings;
        /** @noinspection NullPointerExceptionInspection */
        return isset($settings['mutate'])?$this->getArrayHelper()->parse($settings['mutate'], $extra):[$key, $settings];

    }

    /**
     * @param array $permissions
     * @param array $extra
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function closurePermission (array $permissions, array $extra, bool $noisy = true):bool
    {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($permissions['closure']) && $this->getArrayHelper()->parse($permissions['closure'], $extra) === false);

        if ($allowed === false && $noisy === true) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('closureFails')['message']));
        }
        return $allowed;
    }

    /**
     * @param string $field
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyFieldFormat (string $field, bool $noisy = true):bool
    {
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
     * @param string $direction
     * @param bool $noisy
     * @return bool
     * @throws \RuntimeException
     */
    public function verifyDirectionFormat (string $direction, bool $noisy = true):bool
    {
        $directionOk = in_array($direction, static::ORDER_BY_DIRECTIONS, true);
        if ($directionOk === false) {
            if ($noisy === false) {
                throw new RuntimeException(sprintf($this->getErrorFromConstant('directionNotAllow')['message'], $direction));
            }
            return false;
        }
        return true;
    }



    /**
     * @param string $operator
     * @throws \RuntimeException
     */
    protected function verifyOperatorAllowed(string $operator):void
    {
        if (!in_array($operator, static::SAFE_OPERATORS, true)) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('operatorNotSafe')['message'], $operator));
        }
    }

}
?>