<?php
namespace TempestTools\Crud\Orm\Helper;

use ArrayObject;
use RuntimeException;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Crud\Constants\RepositoryEventsConstants;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToQueryBuilderBuilderContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract;
use TempestTools\Crud\Contracts\Orm\Helper\QueryBuilderHelperContract;
use TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException;
use TempestTools\Crud\Orm\Builder\ArrayToQueryBuilderBuilder;
use TempestTools\Crud\Orm\Utility\RepositoryTrait;

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
     * @param array $params
     * @param array $frontEndOptions
     * @param array $optionOverrides
     * @return array
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
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
        $this->checkQueryMaxParams($params, $options, $optionOverrides);
        $qbWrapper = $repo->createQueryWrapper();
        /** @noinspection NullPointerExceptionInspection */
        $eventArgs->getArgs()['results'] = $this->readCore($qbWrapper, $params, $frontEndOptions, $options, $optionOverrides);

        $evm->dispatchEvent(RepositoryEventsConstants::PROCESS_RESULTS_READ, $eventArgs);
        $evm->dispatchEvent(RepositoryEventsConstants::POST_READ, $eventArgs);

        return $eventArgs->getArgs()['results'];
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
     * @param array $params
     * @param array $frontEndOptions
     * @param array $options
     * @param array $optionOverrides
     * @return array
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
     * @throws RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function readCore(QueryBuilderWrapperContract $qb, array $params, array $frontEndOptions, array $options, array $optionOverrides):array
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
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
     */
    protected function verifyAllowed(array $extra):void
    {
        $config = $this->getArray()['permissions'] ?? [];
        $allowed = $config['allowed']?? true;
        /** @noinspection NullPointerExceptionInspection */
        $allowed = $this->getRepository()->getArrayHelper()->parse($allowed, $extra);
        if ($allowed === false) {
            throw QueryBuilderHelperException::readRequestNotAllowed();
        }
    }

    /**
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
        $returnCount = $frontEndOptions['options']['returnCount'] ?? static::DEFAULT_RETURN_COUNT;
        $hydrate = $this->findSetting([$options, $optionOverrides], 'hydrate');
        /** @noinspection NullPointerExceptionInspection */
        $fetchJoin = isset($this->getArray()['read']['fetchJoin']) ? $this->getRepository()->getArrayHelper()->parse($this->getArray()['read']['fetchJoin'], $extra): static::DEFAULT_FETCH_JOIN;

        $cacheSettings = $this->buildCacheSettings($extra);
        return $qb->getResult($paginate, $returnCount, $hydrationType, $fetchJoin, $cacheSettings, $hydrate);


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
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
     */
    protected function verifyLimitAndOffset (int $limit, array $extra):void
    {
        $maxLimit = $this->getArray()['permissions']['maxLimit'] ?? static::DEFAULT_MAX_LIMIT;
        /** @noinspection NullPointerExceptionInspection */
        $maxLimit = (int)$this->getRepository()->getArrayHelper()->parse($maxLimit, $extra);
        if ($limit > $maxLimit) {
            throw QueryBuilderHelperException::maxLimitHit($limit, $maxLimit);
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
        $permissions = $this->getArray()['read']['permissions']['groupBy'] ?? [];
        foreach ($groupBys as $key => $value) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $value = $this->verifyFrontEndGroupBys($qb, $value, $permissions, $extra);
            $qb->groupBy($value);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */


    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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
            [$key, $value] = $this->verifyPlaceholders($key, $value, $arrayHelper, $permissions, $extra);
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
     * @param string $key
     * @param array $value
     * @param ArrayHelperContract $arrayHelper
     * @param array $permissions
     * @param array $extra
     * @return array
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\QueryBuilderHelperException
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
        $allowQueryCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'allowQueryCache') ?? true;
        $useQueryCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'useQueryCache') ?? true;
        $useResultCache = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'useResultCache') ?? false;
        $timeToLive = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'timeToLive') ?? null;
        $cacheId = $this->findSetting([$cacheSettings, $options, $optionOverrides], 'cacheId') ?? null;

        if ($allowQueryCache === true) {
            $arrayHelper = $this->getRepository()->getArrayHelper();
            /** @noinspection NullPointerExceptionInspection */
            $useQueryCache = $arrayHelper->parse($useQueryCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $useResultCache = $arrayHelper->parse($useResultCache, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $timeToLive = $arrayHelper->parse($timeToLive, $extra);
            /** @noinspection NullPointerExceptionInspection */
            $cacheId = $arrayHelper->parse($cacheId, $extra);
            return ['useQueryCache'=>$useQueryCache, 'useResultCache'=>$useResultCache, 'timeToLive'=>$timeToLive, 'cacheId'=>$cacheId, 'queryCacheDriver'=>$queryCacheDriver, 'resultCacheDriver'=>$resultCacheDriver];
            //$qb->setCacheSettings($useQueryCache, $useResultCache, $timeToLive, $cacheId, $queryCacheDriver, $resultCacheDriver);
        }
        return [];
    }

    /**
     * @param \TempestTools\Crud\Contracts\Orm\Wrapper\QueryBuilderWrapperContract $qb
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