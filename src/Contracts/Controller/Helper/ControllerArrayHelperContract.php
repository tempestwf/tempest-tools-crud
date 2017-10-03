<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/28/2017
 * Time: 7:03 PM
 */

namespace TempestTools\Crud\Contracts\Controller\Helper;

use ArrayObject;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Controller\ControllerContract;

interface ControllerArrayHelperContract extends ArrayHelperContract
{

    /**
     * @param array $input
     * @param array $json
     * @param null $id
     * @return ArrayObject
     */
    public function transformGetRequest(array $input, array $json, $id = null): ArrayObject;

    /**
     * @param array $input
     * @param $id
     * @return ArrayObject
     */
    public function transformNoneGetRequest(array $input, $id = null): ArrayObject;

    /**
     * Makes sure the repo is ready to run
     *
     * @internal param array $optionOverrides
     * @throws \RuntimeException
     */
    public function start():void;

    /**
     * Makes sure every wraps up
     *
     * @param bool $failure
     * @internal param array $optionOverrides
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \RuntimeException
     */
    public function stop($failure = false):void;

    /**
     * @return ControllerContract
     */
    public function getController(): ControllerContract;

    /**
     * @param ControllerContract $controller
     */
    public function setController(ControllerContract $controller): void;
}