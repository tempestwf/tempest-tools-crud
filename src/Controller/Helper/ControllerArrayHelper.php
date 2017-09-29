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

class ControllerArrayHelper extends ArrayHelper implements ControllerArrayHelperContract
{
    /** @var ControllerContract  $controller*/
    protected $controller;

    public function __construct($array = null, ControllerContract $controller)
    {
        $this->setController($controller);
        parent::__construct($array);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array $input
     * @param array $json
     * @param null $id
     * @return ArrayObject
     */
    public function transformGetRequest (array $input, array $json, $id = null):ArrayObject
    {
        $queryLocation = $input['queryLocation'] ?? 'params';
        $query = [];
        $options = [];
        switch ($queryLocation) {
            case 'body':
                $params = $json;
                $query = $params['query'];
                $options = $params['options'];
                break;
            case 'singleParam':
                $params = $json;
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
        $overrides = $options['overrides'] ?? [];

        if ($id !== null) {
            $alias = $controllerOptions['alias'] ?? $controller->getRepo()->getEntityAlias();
            $query = [
                'where'=>[
                    [
                        'field'=>$alias . '.id',
                        'type'=>'and',
                        'operator'=>'eq',
                        'arguments'=>[$id]
                    ],
                ]
            ];
        }

        return new ArrayObject(['self'=>$this, 'query'=>$query, 'frontEntOptions'=>$options, 'controllerOptions'=>$controllerOptions, 'overrides'=>$overrides, 'controller'=>$controller]);
    }

    /**
     * @param array $input
     * @param $id
     * @return ArrayObject
     */
    public function transformNoneGetRequest (array $input, $id = null):ArrayObject
    {
        $params = $input['params'] ?? [];
        $options = $input['options'] ?? [];
        $controller = $this->getController();
        $controllerOptions = array_replace_recursive($this->getArray()->getArrayCopy(), $controller->getOverrides())??[];
        $overrides = $options['overrides'] ?? [];
        if ($id !== null ) {
            if ($options['simplifiedParams'] !== null && $options['simplifiedParams'] === true) {
                $params['id'] = $id;
            } else {
                $params = [$id=>$params];
            }

        }
        return new ArrayObject(['self'=>$this, 'params'=>$params, 'frontEntOptions'=>$options, 'overrides'=>$overrides, 'controllerOptions'=>$controllerOptions, 'controller'=>$controller]);
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
        ], 'transaction');

        if ($transaction !== false) {
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

        /** @noinspection NullPointerExceptionInspection */
        $transaction = $this->findSetting([
            $this->getArray(),
            $controller->getOverrides(),
        ], 'transaction');


        if (
            $transaction !== false
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
