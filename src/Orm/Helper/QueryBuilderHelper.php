<?php
namespace TempestTools\Crud\Orm\Helper;

use RuntimeException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Common\Helper\ArrayHelperTrait;
use TempestTools\Common\Utility\ErrorConstantsTrait;
use TempestTools\Common\Utility\TTConfigTrait;
use \TempestTools\Crud\Contracts\QueryBuilderWrapperContract;
use \TempestTools\Crud\Contracts\QueryBuilderHelperContract;

class QueryBuilderHelper extends ArrayHelper implements QueryBuilderHelperContract
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
        'orderByNotAllowed'=>[
            'message'=>'Error: Order by not allowed. field = %s, direction = %s',
        ],
        'groupByNotAllowed'=>[
            'message'=>'Error: Group by not allowed. field = %s',
        ],
        'maxLimitHit'=>[
            'message'=>'Error: Requested limit greater than max. limit = %s, max = %s',
        ],
        'closureFails' => [
            'message' => 'Error: A validation closure did not pass while building query.',
        ],
        'readRequestNotAllowed' => [
            'message' => 'Error: Read request not allowed.',
        ],

    ];

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


    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $params
     * @param array $frontEndOptions
     * @param array $options
     * @param array $optionOverrides
     * @return array
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read(QueryBuilderWrapperContract $qb, array $params, array $frontEndOptions, array $options, array $optionOverrides):array
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
    protected function verifyAllowed(array $extra):void
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    public function prepareResult (QueryBuilderWrapperContract $qb, array $extra):array
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

        return $qb->getResult($paginate, $returnCount, $hydrationType, $fetchJoin);


    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $frontEndOptions = $extra['frontEndOptions'];

        $limit = isset($frontEndOptions['limit']) ? (int)$frontEndOptions['limit']:static::DEFAULT_LIMIT;
        $offset = isset($frontEndOptions['offset']) ? (int)$frontEndOptions['offset']:static::DEFAULT_OFFSET;

        $this->verifyLimitAndOffset($limit, $extra);
        $qb->setLimitAndOffset($limit, $offset);
    }


    /**
     * @param int $limit
     * @param array $extra
     * @internal param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    protected function verifyLimitAndOffset (int $limit, array $extra):void
    {
        $maxLimit = $this->getArray()['permissions']['maxLimit'] ?? static::DEFAULT_MAX_LIMIT;
        /** @noinspection NullPointerExceptionInspection */
        $maxLimit = (int)$this->getArrayHelper()->parse($maxLimit, $extra);
        if ($limit > $maxLimit) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('maxLimitHit')['message'], $limit, $maxLimit));
        }
    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params']??[];
        $groupBys = $params['query']['groupBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['groupBy'] ?? [], $extra) ?? [];
        foreach ($groupBys as $key => $value) {
            $fastMode = $this->highLowSettingCheck($permissions, $permissions['fields'][$key] ?? [], 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndGroupBys($qb, $key, $permissions, $extra);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $value] = $this->closureMutate($key, $value, $extra);
            }
            $qb->groupBy($key);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param QueryBuilderWrapperContract $qb
     * @param string $key
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    protected function verifyFrontEndGroupBys (QueryBuilderWrapperContract $qb, string $key, array $permissions, array $extra):void
    {
        $this->closurePermission($permissions, $extra);
        $qb->verifyFieldFormat($key);

        $allowed = $this->permissiveAllowedCheck($permissions, $permissions['fields'][$key] ?? []);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('groupByNotAllowed')['message'], $key));
        }
    }


    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params'];
        $orderBys = $params['query']['orderBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['orderBy'] ?? [], $extra) ?? [];
        foreach ($orderBys as $key => $value) {
            $fastMode = $this->highLowSettingCheck($permissions, $permissions['fields'][$key] ?? [], 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndOrderBys($qb, $key, $value, $permissions, $extra);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $value] = $this->closureMutate($key, $value, $extra);
            }
            $qb->orderBy($key, $value);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param string $key
     * @param string $value
     * @param array $permissions
     * @param $extra
     * @throws \RuntimeException
     */
    protected function verifyFrontEndOrderBys (QueryBuilderWrapperContract $qb, string $key, string $value, array $permissions, $extra):void
    {
        $this->closurePermission($permissions, $extra);
        $qb->verifyFieldFormat($key);
        $fieldSettings = $permissions['fields'][$key]??[];
        $qb->verifyDirectionFormat($value);
        $allowed = $this->permissivePermissionCheck($permissions, $fieldSettings, 'directions', $value);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('orderByNotAllowed')['message'], $key, $value));
        }

    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['where']??[], $extra) ?? [];
        $wheres = $params['query']['where'] ?? [];
        foreach ($wheres as $where) {
            $type = !isset($where['type'])?'where':null;
            $string = $this->buildFilterFromFrontEnd($qb, $where, $permissions, $extra);
            $qb->where($type, $string);
        }
    }


    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilderWrapperContract $qb, array $extra):void {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArrayHelper()->parse($this->getArray()['permissions']['having'] ?? [], $extra);
        $havings = $params['query']['having'] ?? [];
        foreach ($havings as $having) {
            $type = !isset($having['type'])?'having':null;
            $string = $this->buildFilterFromFrontEnd($qb, $having, $permissions, $extra);
            $qb->having($type, $string);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $condition
     * @param array $permissions
     * @param array $extra
     * @return string
     * @throws \RuntimeException
     */
    protected function buildFilterFromFrontEnd (QueryBuilderWrapperContract $qb, array $condition, array $permissions, array $extra):string
    {
        $operator = $condition['operator'];
        $fieldName = $condition['field'];
        $arguments = $condition['arguments']??[];
        $conditions = $condition['conditions']??[];
        $fieldSettings = $permissions['fields'][$fieldName] ?? [];

        if ($operator === 'andX' || $operator === 'orX') {
            $string = $this->buildFilterFromFrontEnd($qb, $conditions, $permissions, $extra);
        } else {
            $arguments = $this->argumentsToPlaceholders($arguments, $extra);
            $fastMode = $this->highLowSettingCheck($permissions, $fieldSettings, 'fastMode');
            if ($fastMode !== true) {
                $this->verifyFrontEndCondition($qb, $condition, $permissions);
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$key, $where] = $this->closureMutate(null, $condition, $extra);
            }
            array_unshift($arguments, $fieldName);
            $string = $qb->useExpression($operator, $arguments);
        }
        return $string;
    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $arguments
     * @return array
     */
    protected function argumentsToPlaceholders(QueryBuilderWrapperContract $qb, array $arguments):array
    {
        $result = [];
        foreach ($arguments as $argument) {
            $placeholderName = uniqid('', true);
            $result[] = ':' . $placeholderName;
            $qb->setParameter($placeholderName, $argument);
        }
        return $result;
    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $condition
     * @param array $permissions
     * @throws \RuntimeException
     */
    protected function verifyFrontEndCondition (QueryBuilderWrapperContract $qb, array $condition, array $permissions):void
    {
        $extra = ['condition'=>$condition, 'permissions'=>$permissions];
        $fieldName = $condition['field'];
        $operator = $condition['operator'];
        $qb->verifyFieldFormat($fieldName);
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilderWrapperContract $qb, array $extra):void
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
    protected function verifyPlaceholders (string $key, array $value, array $permissions , array $extra):void
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function applyCachingToQuery (QueryBuilderWrapperContract $qb, array $extra):void
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
            $useQueryCache = $this->getArrayHelper()->parse($useQueryCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $useResultCache = $this->getArrayHelper()->parse($useResultCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $timeToLive = $this->getArrayHelper()->parse($timeToLive, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $cacheId = $this->getArrayHelper()->parse($cacheId, $extra);
            $qb->setCacheSettings($useQueryCache, $useResultCache, $timeToLive, $cacheId, $queryCacheDriver, $resultCacheDriver);
        }

        //TODO: Add tagging in later version
    }

    /**
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilderWrapperContract $qb, array $extra):void
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
                            /** @var string $value */
                            /** @noinspection NullPointerExceptionInspection */
                            $value = $this->getArrayHelper()->parse($value, $extra);
                            if ($firstSelect === true) {
                                $qb->select($value, false);
                                $firstSelect = false;
                            } else {
                                $qb->select($value);
                            }
                            break;
                        case 'from':
                            $value = $this->processFrom($value, $qb, $extra);
                            if ($value['append'] === false) {
                                $qb->from($value['className'], $value['alias'], $value['indexBy']);
                            } else {
                                $qb->from($value['className'], $value['alias'], $value['indexBy'], true);
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
                                $qb->where($value['type'], $where);
                            } else {
                                $qb->where($value['type'], $where, false);
                            }
                            break;
                        case 'having':
                            /** @noinspection NullPointerExceptionInspection */
                            $having = is_array($value['value']) && isset($value['value']['arguments'])?$this->processQueryPartExpr($value['value'], $qb, $extra):$this->getArrayHelper()->parse($value['value'], $extra);
                            if (isset($value['type'])) {
                                $qb->having($value['type'], $having);
                            } else {
                                $qb->having($value['type'], $having, false);
                            }
                            break;
                        case 'orderBy':
                            $value = $this->processOrderParams($value, $qb, $extra);
                            $qb->orderBy($value['sort'], $value['order']);
                            break;
                        case 'groupBy':
                            /** @noinspection NullPointerExceptionInspection */
                            $value = $this->getArrayHelper()->parse($value, $extra);
                            /** @var string $value */
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     */
    protected function processQueryPartArray (array $array, array $defaults, QueryBuilderWrapperContract $qb, array $extra):array
    {
        foreach ($array as $key => $value) {
            /** @noinspection NullPointerExceptionInspection */
            $array[$key] = is_array($value) && isset($value['arguments'])?$this->processQueryPartExpr($value, $qb, $extra):$array[$key] = $this->getArrayHelper()->parse($value, $extra);
        }
        return array_replace($defaults, $array);
    }

    /**
     * @param array $array
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     */
    protected function processJoinParams(array $array, QueryBuilderWrapperContract $qb, array $extra):array
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     */
    protected function processOrderParams(array $array, QueryBuilderWrapperContract $qb, array $extra):array
    {
        $defaults = [
            'sort'=>null,
            'order'=>null
        ];
        return $this->processQueryPartArray($array, $defaults, $qb, $extra);
    }

    /**
     * @param array $array
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     */
    protected function processFrom(array $array, QueryBuilderWrapperContract $qb, array $extra):array
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
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return string|null
     */
    protected function processQueryPartExpr($value, QueryBuilderWrapperContract $qb, array $extra):?string
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
        unset($argument);
        /** @var string $expr */
        $expr = $value['expr'];
        return $qb->useExpression($expr, $value['arguments']);
    }

    /**
     * @param $key
     * @param array $settings
     * @param array $extra
     * @return array
     */
    protected function closureMutate (string $key=null, array $settings=null, array $extra):array
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
    protected function closurePermission (array $permissions, array $extra, bool $noisy = true):bool
    {
        /** @noinspection NullPointerExceptionInspection */
        $allowed = !(isset($permissions['closure']) && $this->getArrayHelper()->parse($permissions['closure'], $extra) === false);

        if ($allowed === false && $noisy === true) {
            throw new RuntimeException(sprintf($this->getErrorFromConstant('closureFails')['message']));
        }
        return $allowed;
    }







}
?>