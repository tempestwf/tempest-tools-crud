<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/28/2017
 * Time: 6:00 PM
 */

namespace TempestTools\Crud\Controller\Helper;

use ArrayObject;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Crud\Contracts\Controller\ControllerContract;
use TempestTools\Crud\Contracts\Controller\Helper\ControllerArrayHelperContract;

class ControllerArrayHelper extends ArrayHelper implements ControllerArrayHelperContract
{
    /** @var ControllerContract  $controller*/
    protected $controller;

    /**
     * ControllerArrayHelper constructor.
     *
     * @param ArrayObject|null $array |null $array
     * @param ControllerContract $controller
     */
    public function __construct(ArrayObject $array = null, ControllerContract $controller)
    {
        $this->setController($controller);
        parent::__construct($array);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array $input
     * @param array $json
     * @param array $resourceIds
     * @param null $id
     * @return ArrayObject
     */
    public function transformGetRequest (array $input, array $json, array $resourceIds = [], $id = null):ArrayObject
    {
        $id = $id === 'batch'?null:$id;
        $queryLocation = $input['queryLocation'] ?? $id === null?'params':null;
        [$query, $options] = $this->paramsToParamsAndOptions($input, $json, $queryLocation);
        $controller = $this->getController();
        $controllerOptions = array_replace_recursive($this->getArray()->getArrayCopy(), $controller->getOverrides())??[];
        $overrides = $controllerOptions['overrides'] ?? [];
        $options['resourceIds'] = $options['resourceIds'] ?? [];
        $options['resourceIds'] = array_replace_recursive($resourceIds, $options['resourceIds']);
        $query = $this->resourceIdsToPlaceholders($query, $options, $controllerOptions, $queryLocation);
        if ($id !== null) {
            $query = $this->injectIdOnGetRequest($query, $queryLocation, $id);
        }

        return new ArrayObject(['self'=>$this, 'query'=>$query, 'frontEndOptions'=>$options, 'controllerOptions'=>$controllerOptions, 'overrides'=>$overrides, 'controller'=>$controller]);
    }

    protected function injectIdOnGetRequest(array $query, string $queryLocation, $id):array
    {
        $alias = $controllerOptions['alias'] ?? $this->getController()->getRepo()->getEntityAlias();
        // The only where clause from the front end filter should be this one based on the id
        if ($queryLocation === 'params') {
            /**
             * @var array $query
             */
            foreach ($query as $key => $value) {
                if (preg_match('/^(and|or)_where/', $key)) {
                    unset($query[$key]);
                }
            }
            $query['and_where_eq_' . $alias . '-id'] = $id;
        } else {

            $query['query'] = $query['query'] ?? [];
            $query['query']['where'] = [];
            $query['query']['where'][] = [
                'field'=>$alias . '.id',
                'type'=>'and',
                'operator'=>'eq',
                'arguments'=>[$id],
            ];
        }
        return $query;
    }

    /**
     * @param array $input
     * @param array $json
     * @param string $queryLocation
     * @return array
     * @internal param array $params
     */
    protected function paramsToParamsAndOptions(array $input, array $json, string $queryLocation):array
    {
        $query = [];
        $options = [];

        switch ($queryLocation) {
            case 'body':
                $params = $json;
                $query = $params['query'];
                $options = $params['options'];
                break;
            case 'singleParam':
                $params = json_decode($input['query'], true);
                $query = $params['query'];
                $options = $params['options'];
                break;
            case 'params':
                $query = $input;
                unset($query['queryLocation']);
                $options = ['useGetParams'=>true];
                break;
        }
        return [$query, $options];
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param array $query
     * @param array $options
     * @param array $controllerOptions
     * @param string $queryLocation
     * @return array
     */
    protected function resourceIdsToPlaceholders (array $query, array $options, array $controllerOptions, string $queryLocation):array
    {
        if (isset($controllerOptions['resourceIdConversion']) === true) {
            if ($queryLocation !== 'params') {
                $query['query']['placeholders'] = $query['query']['placeholders'] ?? [];
            }
            /**
             * @var array $controllerOptions
             * @var array $resourceIdConversion
             */
            $resourceIdConversion = $controllerOptions['resourceIdConversion'];
            foreach ($resourceIdConversion as $key => $value) {
                if ($queryLocation === 'params') {
                    if ($value === null) {
                        $query['placeholder_' . $key . 'ResourceId'] = $options['resourceIds'][$key];
                    } else {
                        $query['placeholder_' . $value] = $options['resourceIds'][$key];
                    }
                } else {
                    if ($value === null) {
                        $query['query']['placeholders'][$key . 'ResourceId'] = $options['resourceIds'][$key];
                    } else {
                        $query['query']['placeholders'][$value] = $options['resourceIds'][$key];
                    }
                }
            }
        }
        return $query;
    }


    /**
     * @param array $input
     * @param array $resourceIds
     * @param $id
     * @return ArrayObject
     */
    public function transformNoneGetRequest (array $input, array $resourceIds = [], $id = null):ArrayObject
    {
        $id = $id === 'batch'?null:$id;
        $params = $input['params'] ?? [];
        $options = $input['options'] ?? [];
        $options['resourceIds'] = $options['resourceIds'] ?? [];
        $options['resourceIds'] = array_replace_recursive($resourceIds, $options['resourceIds']);
        $controller = $this->getController();
        $controllerOptions = array_replace_recursive($this->getArray()->getArrayCopy(), $controller->getOverrides())??[];
        $overrides = $controllerOptions['overrides'] ?? [];
        if ($id !== null ) {
            $params = $this->injectIdOnNonGetRequest($options, $params, $id);

        }
        return new ArrayObject(['self'=>$this, 'params'=>$params, 'frontEndOptions'=>$options, 'overrides'=>$overrides, 'controllerOptions'=>$controllerOptions, 'controller'=>$controller]);
    }

    /**
     * @param array $options
     * @param array $params
     * @param $id
     * @return array
     */
    protected function injectIdOnNonGetRequest(array $options, array $params, $id):array
    {
        if (isset($options['simplifiedParams']) === true && $options['simplifiedParams'] === true) {
            $params['id'] = $id;
            $params = [$params];
        } else {
            $params = [$id=>$params];
        }
        return $params;
    }

    /**
     * Makes sure the repo is ready to run
     *
     * @throws \RuntimeException
     */
    public function start():void
    {
        $controller = $this->getController();
        $repo = $controller->getRepo();

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $this->findSetting([
            $this->getArray(),
            $controller->getOverrides(),
        ], 'transaction') ?? false;

        if ($transaction === true) {
            /** @noinspection NullPointerExceptionInspection */
            $repo->getEm()->beginTransaction();
        }
    }

    /**
     * Makes sure every wraps up
     *
     * @param bool $failure
     * @internal param array $optionOverrides
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \RuntimeException
     */
    public function stop($failure = false):void
    {
        $controller = $this->getController();
        $repo = $controller->getRepo();
        $em = $repo->getEm();
        if ($em !== null) {
            /** @noinspection NullPointerExceptionInspection */
            $transaction = $this->findSetting([
                $this->getArray(),
                $controller->getOverrides(),
            ], 'transaction') ?? false;

            if (
                $transaction === true
            ) {
                if ($failure === true) {
                    /** @noinspection NullPointerExceptionInspection */
                    $em->rollBack();
                } else {
                    /** @noinspection NullPointerExceptionInspection */
                    $em->commit();
                }
            }
        }

    }

    /**
     * @return ControllerContract
     */
    public function getController(): ControllerContract
    {
        return $this->controller;
    }

    /**
     * @param ControllerContract $controller
     */
    public function setController(ControllerContract $controller):void
    {
        $this->controller = $controller;
    }

}
