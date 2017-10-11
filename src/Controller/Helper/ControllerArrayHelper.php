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
        $controller = $this->getController();
        $controllerOptions = array_replace_recursive($this->getArray()->getArrayCopy(), $controller->getOverrides())??[];
        $overrides = $controllerOptions['overrides'] ?? [];
        $options['resourceIds'] = $options['resourceIds'] ?? [];
        $options['resourceIds'] = array_replace_recursive($resourceIds, $options['resourceIds']);
        if ($id !== null) {
            $alias = $controllerOptions['alias'] ?? $controller->getRepo()->getEntityAlias();
            $query['query'] = $query['query'] ?? [];
            $query['query']['where'] = $query['query']['where'] ?? [];
            $query['query']['where'][] = [
                'field'=>$alias . '.id',
                'type'=>'and',
                'operator'=>'eq',
                'arguments'=>[$id]
            ];
        }

        return new ArrayObject(['self'=>$this, 'query'=>$query, 'frontEndOptions'=>$options, 'controllerOptions'=>$controllerOptions, 'overrides'=>$overrides, 'controller'=>$controller]);
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
            if (isset($options['simplifiedParams']) === true && $options['simplifiedParams'] === true) {
                $params['id'] = $id;
                $params = [$params];
            } else {
                $params = [$id=>$params];
            }

        }
        return new ArrayObject(['self'=>$this, 'params'=>$params, 'frontEndOptions'=>$options, 'overrides'=>$overrides, 'controllerOptions'=>$controllerOptions, 'controller'=>$controller]);
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
