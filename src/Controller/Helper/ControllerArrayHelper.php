<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/28/2017
 * Time: 6:00 PM
 */

namespace TempestTools\Scribe\Controller\Helper;

use ArrayObject;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Scribe\Contracts\Controller\ControllerContract;
use TempestTools\Scribe\Contracts\Controller\Helper\ControllerArrayHelperContract;
use TempestTools\Scribe\Exceptions\Laravel\Controller\ControllerException;

/**
 * A helper used for controllers
 *
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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

    /**
     * Checks if the current action is allowed.
     *
     * @param string $action
     * @throws \TempestTools\Scribe\Exceptions\Laravel\Controller\ControllerException
     */
    public function checkAllowed (string $action):void {
        if ($action === 'index') {
            $allowed = $this->getArray()['allowIndex'] ?? true;
        } else {
            $allowed = $this->getArray()['allowed'] ?? true;
        }
        $allowed = $this->getController()->getArrayHelper()->parse($allowed, ['self'=>$this]);
        if ($allowed === false) {
            throw ControllerException::actionNotAllowed($action);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Normalizes a get request into all the information needed for the repository and controller to respond to it
     * @param array $input
     * @param array $json
     * @param array $resourceIds
     * @param null $id
     * @return ArrayObject
     */
    public function transformGetRequest (array $input, array $json, array $resourceIds = [], $id = null):ArrayObject
    {
        $id = $id === 'batch'?null:$id;
        $queryLocation = $input['queryLocation'] ?? 'params';
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

    /**
     * Injects the id that was passed to the route as a where filter that will be passed to the repository.
     * @param array $query
     * @param string $queryLocation
     * @param $id
     * @return array
     */
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
     * Divides params passed to it into a params array and an options array and returns it. This method also figures out where to pull the query data from and extracts it from there
     * @param array $input
     * @param array $json
     * @param string $queryLocation
     * @return array
     */
    protected function paramsToParamsAndOptions(array $input, array $json, string $queryLocation):array
    {
        $query = [];
        $options = [];

        switch ($queryLocation) {
            case 'body':
                $params = $json;
                $query = $params['query'] ?? [];
                $query = ['query'=>$query];
                $options = $params['options'] ?? [];
                break;
            case 'singleParam':
                $params = json_decode($input['query'] ?? [], true);
                $query = $params['query'] ?? [];
                $query = ['query'=>$query];
                $options = $params['options'] ?? [];
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
     * Takes resource ids that were passed to the route and converts them into placeholder filters to pass to the repository. This facilitates easy adding placeholders to to index queries to filter results based on the resource ids that were passed to the route.
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
                $query['query'] = $query['query'] ??[];
                $query['query']['placeholders'] = $query['query']['placeholders'] ?? [];
            }
            /**
             * @var array $controllerOptions
             * @var array $resourceIdConversion
             */
            $resourceIdConversion = $controllerOptions['resourceIdConversion'];
            foreach ($resourceIdConversion as $key => $value) {
                if ($value !== null) {
                    if ($queryLocation === 'params') {
                        $query['placeholder_' . $value] = $options['resourceIds'][$key];
                    } else {
                        $query['query']['placeholders'][$value] = $options['resourceIds'][$key];
                    }
                }
            }
        }
        return $query;
    }


    /**
     * Normalizes a non get request into all the information needed for repository and controller to respond to it
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
     * On a non get request this takes the id that was passed to the route and stores it in the params we pass to the repository.
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
     * Before interacting with the repo the controller can make preparations (such as wrapping all actions in an additional transaction -- a useful feature if additional interactions with the database are going to happen via event listeners).
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
     * Makes sure every thing wraps up
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
