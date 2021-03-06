<?php
namespace TempestTools\Scribe\Orm\Helper;

use ArrayObject;
use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Scribe\Constants\RepositoryEventsConstants;
use TempestTools\Scribe\Contracts\Orm\Builder\ArrayToQueryBuilderBuilderContract;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;
use TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Scribe\Contracts\Orm\Helper\QueryBuilderHelperContract;
use TempestTools\Scribe\Exceptions\Orm\Helper\QueryBuilderHelperException;
use TempestTools\Scribe\Orm\Builder\ArrayToQueryBuilderBuilder;
use TempestTools\Scribe\Orm\Utility\RepositoryTrait;

/**
 * A array helper class with functionality for building queries
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
class QueryBuilderHelper extends ArrayHelper implements QueryBuilderHelperContract
{
    use RepositoryTrait;

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

    /** @var ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder*/
    protected $arrayToQueryBuilderBuilder;

    /**
     * ArrayHelper constructor.
     *
     * @param ArrayObject|null $array
     * @param RepositoryContract $repository
     * @param ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder
     */
    public function __construct(ArrayObject $array = NULL, RepositoryContract $repository, ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder = null)
    {
        $arrayToQueryBuilderBuilder = $arrayToQueryBuilderBuilder ?? new ArrayToQueryBuilderBuilder();
        $this->setRepository($repository);
        $this->setArrayToQueryBuilderBuilder($arrayToQueryBuilderBuilder);

        parent::__construct($array);
    }


    /**
     * Handles a read operation
     * @param array $params
     * @param array $frontEndOptions
     * @param array $optionOverrides
     * @return array
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\QueryBuilderHelperException
     * @throws \Doctrine\ORM\ORMException
     * @throws \RuntimeException
     */
    public function read (array $params=[], array $frontEndOptions=[], array $optionOverrides = []):array
    {
        $repo = $this->getRepository();
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs = $repo->makeEventArgs($params, $optionOverrides, $frontEndOptions);
        $eventArgs->getArgs()['action'] = 'read';
        $evm = $repo->getEventManager();
        $evm->dispatchEvent(RepositoryEventsConstants::PRE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VALIDATE_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::VERIFY_READ, $eventArgs);
        /** @var array $params */
        $params = $eventArgs->getArgs()['params'];
        $options = $eventArgs->getArgs()['options'];
        $optionOverrides = $eventArgs->getArgs()['optionOverrides'];
        $frontEndOptions = $eventArgs->getArgs()['frontEndOptions'];
        [$params, $frontEndOptions] = $this->convertGetParams($params, $frontEndOptions);

        $this->checkQueryMaxParams($params, $options, $optionOverrides);
        $qbWrapper = $repo->createQueryWrapper();
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs->getArgs()['results'] = $this->readCore($qbWrapper, $params, $frontEndOptions, $options, $optionOverrides);

        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_READ, $eventArgs);

        return $eventArgs->getArgs()['results'];
    }

    /**
     * Converts get params to filter and options in the standard format
     * @param array $params
     * @param array $frontEndOptions
     * @return array
     */
    protected function convertGetParams(array $params, array $frontEndOptions):array
    {
        $convert = $frontEndOptions['useGetParams']??false;
        $convert = (bool)$convert;
        if ($convert === true) {
            return $this->doConvertGetParams($params, $frontEndOptions);
        }

        return [$params, $frontEndOptions];
    }

    /**
     * Handles the nitty gritty for conversion of get params
     * @param array $params
     * @param array $frontEndOptions
     * @return array
     */
    protected function doConvertGetParams(array $params, array $frontEndOptions):array
    {
        $query = [];
        foreach ($params as $key => $value) {
            $key = str_replace('-', '.', $key);
            if (preg_match('/^((and|or)_(where|having))|(orderBy|groupBy|option|placeholder)/', $key)) {
                $parts = explode('_', $key);
                if ($parts[0] === 'and' || $parts[0] === 'or') {
                    if ($parts[1] === 'where') {
                        if (isset($query['where']) === false) {
                            $query['where'] = [];
                        }
                        $query['where'][] = $this->doConvertFilterParam($parts, $value);
                    } else if ($parts[1] === 'having') {
                        if (isset($query['having']) === false) {
                            $query['having'] = [];
                        }
                        $query['having'][] = $this->doConvertFilterParam($parts, $value);

                    }
                } else if ($parts[0] === 'orderBy') {
                    if (isset($query['orderBy']) === false) {
                        $result['orderBy'] = [];
                    }
                    $query['orderBy'][$parts[1]] = $value;
                } else if ($parts[0] === 'groupBy') {
                    $value = str_replace('-', '.', $value);
                    $query['groupBy'] = $value;
                } else if ($parts[0] === 'placeholder') {
                    if (isset($query['placeholders']) === false) {
                        $query['placeholders'] = [];
                    }
                    $placeholder = [
                        'value'=>$value
                    ];
                    if (isset($parts[2]) === true) {
                        $placeholder['type'] = $parts[2];
                    }
                    $query['placeholders'][$parts[1]] = $placeholder;
                } else if ($parts[0] === 'option' && isset($frontEndOptions[$parts[1]]) === false) {
                    $value = $parts[1] === 'returnCount'?(bool)$value:$value;
                    $frontEndOptions[$parts[1]] = $value;
                }
            }
        }
        return [['query'=>$query],$frontEndOptions];
    }


    /**
     * Handles the conversion of filter params when converting get params to the standard format
     * @param array $parts
     * @param $value
     * @return array
     */
    protected function doConvertFilterParam(array $parts, $value):array
    {
        $condition = [];
        if ($parts[0] === 'and' || $parts[0] === 'or') {
            $condition['type'] = $parts[0];
            $condition['operator'] = $parts[2];
            $condition['field'] = $parts[3] ?? null;

            if ($condition['operator'] === 'andX' || $condition['operator'] === 'orX') {
                $condition['conditions'] = json_decode($value, true);
                $condition['arguments'] = [];
            } else {
                $condition['arguments'] = is_array($value) === true?$value:[$value];
                if ($condition['operator'] === 'in' || $condition['operator'] === 'notIn') {
                    if (is_array($value) === true) {
                        $condition['arguments'] = [$condition['arguments']];
                    } else {
                        $decoded = json_decode($value, true);
                        $condition['arguments'] = [$decoded];
                    }
                } else if ($condition['operator'] === 'isNull' || $condition['operator'] === 'isNotNull') {
                    $condition['arguments'] = [];
                } else if (is_string($value) && $condition['operator'] === 'between') {
                    $condition['arguments'] = json_decode($value, true);
                }
            }
        }
        return $condition;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Handles the nitty gritty of a read operation, calling all the other required methods
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $params
     * @param array $frontEndOptions
     * @param array $options
     * @param array $optionOverrides
     * @return array
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\QueryBuilderHelperException
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function readCore(QueryBuilderWrapperContract $qb, array $params, array $frontEndOptions, array $options, array $optionOverrides):array
    {
        $repo = $this->getRepository();
        $arrayHelper = $repo->getArrayHelper();
        $extra = [
            'options'=>$options,
            'optionOverrides'=>$optionOverrides,
            'frontEndOptions'=>$frontEndOptions,
            'qb'=>$qb,
            'queryHelper'=>$this,
            'arrayHelper'=>$arrayHelper
        ];
        $config = $this->getArray()['read']['permissions'] ?? [];
        $extra['params'] = $this->processSettings(null, $params, $arrayHelper, $config, $extra)[1];
        $this->verifyAllowed($extra);
        $this->buildBaseQuery($qb, $extra);
        $this->addPlaceholders($qb, $extra);
        $this->addFrontEndWhere($qb, $extra);
        $this->addFrontEndHaving($qb, $extra);
        $this->addFrontEndOrderBys($qb, $extra);
        $this->addFrontEndGroupBys($qb, $extra);
        $this->addLimitAndOffset($qb, $extra);
        return $this->prepareResult($qb, $extra);
    }


    /**
     * Verifies the current operation is allowed in the config
     * @param array $extra
     * @throws \RuntimeException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\QueryBuilderHelperException
     */
    protected function verifyAllowed(array $extra):void
    {
        $config = $this->getArray()['read']['permissions'] ?? [];
        $allowed = $config['allowed']?? true;
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getRepository()->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::readRequestNotAllowed();
        }
    }

    /**
     * Gets the result to the query from the DB while making sure all the options passed to the get result method are appropriate
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \RuntimeException
     */
    public function prepareResult (QueryBuilderWrapperContract $qb, array $extra):array
    {
        $options = $extra['options']??[];
        $optionOverrides = $extra['optionOverrides']??[];
        $frontEndOptions = $extra['frontEndOptions']??[];
        $hydrationType = $this->findSetting([$options, $optionOverrides], 'hydrationType');
        $paginate = $this->findSetting([$options, $optionOverrides], 'paginate');
        $fetchJoin = $this->findSetting([$options, $optionOverrides], 'fetchJoin');
        $returnCount = $frontEndOptions['returnCount'] ?? static::DEFAULT_RETURN_COUNT;
        $returnCount = (bool)$returnCount;
        $hydrate = $this->findSetting([$options, $optionOverrides], 'hydrate');
        /** @noinspection NullPointerExceptionInspection */
        $fetchJoin = $fetchJoin !== null ? $this->getRepository()->getArrayHelper()->parse($fetchJoin, $extra): static::DEFAULT_FETCH_JOIN;

        $cacheSettings = $this->buildCacheSettings($extra);
        return $qb->getResult($paginate, $returnCount, $hydrationType, $fetchJoin, $cacheSettings, $hydrate);


    }

    /**
     * Adds a limit and offset to the query
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addLimitAndOffset(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $frontEndOptions = $extra['frontEndOptions'];

        $limit = isset($frontEndOptions['limit']) ? (int)$frontEndOptions['limit']:static::DEFAULT_LIMIT;
        $offset = isset($frontEndOptions['offset']) ? (int)$frontEndOptions['offset']:static::DEFAULT_OFFSET;

        $this->verifyLimitAndOffset($limit, $offset, $extra);
        $qb->setLimitAndOffset($limit, $offset);
    }


    /**
     * Verifies that the limit and offset requested as appropriate based on the config
     * @param int $limit
     * @param int $offset
     * @param array $extra
     * @internal param array $extra
     * @internal param array $extra
     * @throws \RuntimeException
     */
    protected function verifyLimitAndOffset (int $limit, int $offset, array $extra):void
    {
        $options = $extra['options'];
        $optionOverrides = $extra['optionOverrides'];
        $maxLimit = $this->findSetting([$this->getArray()['read']['permissions'] ?? [], $options, $optionOverrides], 'maxLimit');
        $maxLimit = $maxLimit ?? static::DEFAULT_MAX_LIMIT;

        $fixedLimit = $this->findSetting([$this->getArray()['read']['permissions'] ?? [], $options, $optionOverrides], 'fixedLimit');

        /** @noinspection NullPointerExceptionInspection */
        $maxLimit = (int)$this->getRepository()->getArrayHelper()->parse($maxLimit, $extra);

        /** @noinspection NullPointerExceptionInspection */
        $fixedLimit = $fixedLimit!==null?(int)$this->getRepository()->getArrayHelper()->parse($fixedLimit, $extra):null;

        if ($fixedLimit !== null && ($offset + $limit) % $fixedLimit !== 0) {
            throw QueryBuilderHelperException::fixedLimit($limit, $offset, $fixedLimit);
        }

        if ($limit > $maxLimit) {
            throw QueryBuilderHelperException::maxLimitHit($limit, $maxLimit);
        }
    }

    /**
     * Adds group bys requested
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndGroupBys(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params']??[];
        $groupBys = $params['query']['groupBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArray()['read']['permissions']['groupBy'] ?? [];
        foreach ($groupBys as $key => $value) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $value = $this->verifyFrontEndGroupBys($qb, $value, $permissions, $extra);
            $qb->groupBy($value);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * Verifies that the group bys requested are appropriate based on the config
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param string $key
     * @param array $permissions
     * @param array $extra
     * @internal param array $extra
     * @return string
     * @throws \RuntimeException
     * @throws QueryBuilderHelperException
     */
    protected function verifyFrontEndGroupBys (QueryBuilderWrapperContract $qb, string $key, array $permissions, array $extra):string
    {
        $qb->verifyFieldFormat($key);
        $repo = $this->getRepository();
        $arrayHelper = $repo->getArrayHelper();
        $fieldSettings = $permissions['fields'][$key] ?? [];

        /** @noinspection PhpUnusedLocalVariableInspection */
        [$key, $value] = $this->processSettings($key, null, $arrayHelper, $fieldSettings, $extra);

        $allowed = $repo->permissiveAllowedCheck($permissions, $fieldSettings);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $arrayHelper->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::groupByNotAllowed($key);
        }

        return $key;
    }


    /**
     * Adds order bys that were requested
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndOrderBys(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params'];
        $orderBys = $params['query']['orderBy'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getRepository()->getArrayHelper()->parse($this->getArray()['read']['permissions']['orderBy'] ?? [], $extra) ?? [];
        foreach ($orderBys as $key => $value) {
            [$key, $value] = $this->verifyFrontEndOrderBys($qb, $key, $value, $permissions, $extra);
            $qb->orderBy($key, $value);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Verifies the that the requested order bys are appropriate based on the config
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param string $key
     * @param string $value
     * @param array $permissions
     * @param $extra
     * @return array
     * @throws \RuntimeException
     * @throws QueryBuilderHelperException
     */
    protected function verifyFrontEndOrderBys (QueryBuilderWrapperContract $qb, string $key, string $value, array $permissions, $extra):array
    {
        $qb->verifyFieldFormat($key);
        $qb->verifyDirectionFormat($value);
        $repo = $this->getRepository();
        $arrayHelper = $repo->getArrayHelper();
        $fieldSettings = $permissions['fields'][$key]??[];

        /** @noinspection PhpUnusedLocalVariableInspection */
        [$key, $value] = $this->processSettings($key, $value, $arrayHelper, $fieldSettings, $extra);

        $allowed = $repo->permissivePermissionCheck($permissions, $fieldSettings, 'directions', $value);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $arrayHelper->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::orderByNotAllowed($key, $value);
        }
        return [$key, $value];

    }

    /**
     * Adds a requested where filter
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndWhere(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getRepository()->getArrayHelper()->parse($this->getArray()['read']['permissions']['where']??[], $extra) ?? [];
        $wheres = $params['query']['where'] ?? [];
        foreach ($wheres as $where) {
            $type = $where['type'] ?? 'and';
            $string = $this->buildFilterFromFrontEnd($qb, $where, $permissions, $extra);
            $qb->where($type, $string);
        }
    }


    /**
     * Adds a requested having filter
     * @param QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addFrontEndHaving(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $params = $extra['params'];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getRepository()->getArrayHelper()->parse($this->getArray()['read']['permissions']['having'] ?? [], $extra);
        $havings = $params['query']['having'] ?? [];
        foreach ($havings as $having) {
            $type = $having['type'] ?? 'having';
            $string = $this->buildFilterFromFrontEnd($qb, $having, $permissions, $extra);
            $qb->having($type, $string);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Builds the filters based on the request for where or having query parts
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $condition
     * @param array $permissions
     * @param array $extra
     * @return string
     * @throws \RuntimeException
     */
    protected function buildFilterFromFrontEnd (QueryBuilderWrapperContract $qb, array $condition, array $permissions, array $extra):string
    {
        $operator = $condition['operator'];

        if ($operator === 'andX' || $operator === 'orX') {
            $conditions = $condition['conditions']??[];
            $arguments = [];
            foreach ($conditions as $newCondition) {
                $arguments[] = $this->buildFilterFromFrontEnd($qb, $newCondition, $permissions, $extra);
            }
            $string = $qb->useExpression($operator, $arguments);
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$key, $condition] = $this->verifyFrontEndCondition($qb, $condition, $permissions, $extra);
            $operator = $condition['operator'];
            $fieldName = $condition['field'];
            $arguments = $condition['arguments']??[];
            $arguments = $this->argumentsToPlaceholders($qb, $arguments);
            array_unshift($arguments, $fieldName);
            $string = $qb->useExpression($operator, $arguments);
        }
        return $string;
    }

    /**
     * Takes arguments requested and converts them to query placeholders so no data requested is ever directly inserted into the query (stops injection)
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $arguments
     * @return array
     */
    protected function argumentsToPlaceholders(QueryBuilderWrapperContract $qb, array $arguments):array
    {
        $result = [];
        foreach ($arguments as $argument) {
            /** @noinspection ArgumentEqualsDefaultValueInspection */
            $placeholderName = $this->makePlaceholderName($argument);
            $result[] = ':' . $placeholderName;
            $qb->setParameter($placeholderName, $argument);
        }
        return $result;
    }

    /**
     * Makes a unique placeholder name based on the requested data. Unique but repeatable names are important for setting up test cases where the resulting DQL can be compared.
     * @param $value
     * @return string
     */
    public function makePlaceholderName($value):string
    {
        $placeholderName = (string)is_array($value)?json_encode($value):$value;
        return  'placeholder' . substr(md5($placeholderName), 0, 16);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Verifies that the where and having filters requested are appropriate based on the config
     * @param QueryBuilderWrapperContract $qb
     * @param array $condition
     * @param array $permissions
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     */
    protected function verifyFrontEndCondition (QueryBuilderWrapperContract $qb, array $condition, array $permissions, array $extra):array
    {

        $repo = $this->getRepository();
        $arrayHelper = $repo->getArrayHelper();
        $extra['condition'] = $condition;
        $fieldName = $condition['field'];
        $operator = $condition['operator'];
        $qb->verifyFieldFormat($fieldName);
        $fieldSettings = $permissions['fields'][$fieldName] ?? [];
        [$key, $where] = $this->processSettings(null, $condition, $arrayHelper, $fieldSettings, $extra);

        $allowed = $repo->permissivePermissionCheck($permissions, $fieldSettings, 'operators', $operator);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $arrayHelper->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::operatorNotAllowed($fieldName, $operator);
        }
        return [$key, $where];
    }


    /**
     * Adds placeholders to the query
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     * @throws \RuntimeException
     */
    public function addPlaceholders(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $repo = $this->getRepository();
        $arrayHelper = $repo->getArrayHelper();
        $queryPlaceholders = $this->getArray()['read']['settings']['placeholders'] ?? [];
        $frontEndPlaceholders = $extra['params']['query']['placeholders'] ?? [];
        $options = $extra['options']['placeholders'] ?? [];
        $overridePlaceholders = $extra['optionOverrides']['placeholders'] ?? [];
        /** @noinspection NullPointerExceptionInspection */
        $permissions = $this->getArray()['read']['permissions']['placeholders'] ?? [];
        $placeholders = array_replace($queryPlaceholders, $options, $overridePlaceholders);
        $safeKeys = array_keys($placeholders);
        $placeholders = array_replace($frontEndPlaceholders, $placeholders);
        foreach ($placeholders as $key=>$value) {
            if (!in_array($key, $safeKeys, true)) {
                [$key, $value] = $this->verifyPlaceholders($key, $value, $arrayHelper, $permissions, $extra);
            }
            $type = $value['type'] ?? null;
            $value = $value['value'] ?? null;
            if (in_array($key, $safeKeys, true)) {
                /** @noinspection NullPointerExceptionInspection */
                $type = $arrayHelper->parse($type, $extra);
                /** @noinspection NullPointerExceptionInspection */
                $value = $arrayHelper->parse($value, $extra);
            }
            $qb->setParameter($key, $value, $type);
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Verifies that the requested placeholders are appropriate based on the config
     * @param string $key
     * @param array $value
     * @param ArrayHelperContract $arrayHelper
     * @param array $permissions
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     * @throws \TempestTools\Scribe\Exceptions\Orm\Helper\QueryBuilderHelperException
     * @internal param array $extra
     */
    protected function verifyPlaceholders (string $key, array $value, ArrayHelperContract $arrayHelper, array $permissions , array $extra):array
    {
        $fieldSettings = $permissions['placeholderNames'][$key] ?? [];
        [$key, $value] = $this->processSettings($key, $value, $arrayHelper, $fieldSettings, $extra);
        $allowed = $this->getRepository()->permissiveAllowedCheck($permissions, $fieldSettings);
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $arrayHelper->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::placeholderNoAllowed($key);
        }

        return [$key, $value];
    }

    /**
     * Builds cache related settings for the query
     * @param array $extra
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @return array
     */
    public function buildCacheSettings (array $extra):array
    {
        $config = $this->getArray()['read']['settings'] ?? [];
        $options = $extra['options'];
        $cacheSettings = $config['cache'] ?? [];
        $optionOverrides = $extra['optionOverrides'];
        $queryCacheDriver = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'queryCacheDrive') ?? null;
        $resultCacheDriver = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'resultCacheDriver') ?? null;
        $allowCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'allowCache') ?? true;
        $useQueryCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'useQueryCache') ?? true;
        $useResultCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'useResultCache') ?? false;
        $timeToLive = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'timeToLive') ?? null;
        $cacheId = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'cacheId') ?? null;
        $queryCacheProfile = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'queryCacheProfile') ?? null;

        if ($allowCache === true) {
            $arrayHelper = $this->getRepository()->getArrayHelper();
            /** @noinspection NullPointerExceptionInspection */
            $useQueryCache = $arrayHelper->parse($useQueryCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $useResultCache = $arrayHelper->parse($useResultCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $timeToLive = $arrayHelper->parse($timeToLive, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $cacheId = $arrayHelper->parse($cacheId, $extra);
            return compact('useQueryCache', 'useResultCache', 'timeToLive', 'cacheId', 'queryCacheDriver', 'resultCacheDriver', 'queryCacheProfile');
        }
        return [];
    }

    /**
     * Builds the base query via the config
     * @param \TempestTools\Scribe\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $extra
     */
    public function buildBaseQuery(QueryBuilderWrapperContract $qb, array $extra):void
    {
        $config = $this->getArray()['read']['query'] ?? [];
        $builder = $this->getArrayToQueryBuilderBuilder();
        $arrayHelper = $this->getRepository()->getArrayHelper();
        /** @var array $config */
        foreach ($config as $queryPart => $entries) {
            $builder->$queryPart($entries, $qb, $arrayHelper, $extra);
        }
    }

    /**
     * Checks that more query params have not been requested than are allowed in the options
     * @param array $values
     * @param array $options
     * @param array $optionOverrides
     * @throws \RuntimeException
     * @throws QueryBuilderHelperException
     */
    protected function checkQueryMaxParams(array $values, array $options, array $optionOverrides):void
    {
        /** @noinspection NullPointerExceptionInspection */
        $maxBatch = $this->getRepository()->getArrayHelper()->findSetting([
            $options,
            $optionOverrides,
        ], 'queryMaxParams');

        if ($maxBatch !== NULL) {
            $count = count($values, COUNT_RECURSIVE);

            if ($count > $maxBatch) {
                throw QueryBuilderHelperException::moreQueryParamsThanMax($count, $maxBatch);
            }
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Processes settings blocks stored in the config to call the builder in order to process the requested data
     * @param string $key
     * @param $value
     * @param ArrayHelperContract $arrayHelper
     * @param array $permissions
     * @param array $extra
     * @return array
     */
    protected function processSettings(string $key=null, $value=null, ArrayHelperContract $arrayHelper, array $permissions, array $extra):array
    {
        /**@var array[] $permissions*/
        if (isset($permissions['settings'])) {
            $builder = $this->getArrayToQueryBuilderBuilder();
            foreach ($permissions['settings'] as $permissionSetting => $permissionValue) {
                [$key, $value] = $builder->$permissionSetting($key, $value, $arrayHelper, $permissionValue, $extra);
            }
        }
        return [$key, $value];

    }

    /**
     * @return ArrayToQueryBuilderBuilderContract
     */
    public function getArrayToQueryBuilderBuilder(): ArrayToQueryBuilderBuilderContract
    {
        return $this->arrayToQueryBuilderBuilder;
    }

    /**
     * @param ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder
     */
    public function setArrayToQueryBuilderBuilder(ArrayToQueryBuilderBuilderContract $arrayToQueryBuilderBuilder):void
    {
        $this->arrayToQueryBuilderBuilder = $arrayToQueryBuilderBuilder;
    }

}
?>