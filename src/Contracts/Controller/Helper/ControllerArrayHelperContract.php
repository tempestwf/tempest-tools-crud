<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/28/2017
 * Time: 7:03 PM
 */

namespace TempestTools\Scribe\Contracts\Controller\Helper;

use ArrayObject;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Scribe\Contracts\Controller\ControllerContract;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface ControllerArrayHelperContract extends ArrayHelperContract
{
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $input
     * @param array $json
     * @param array $resourceIds
     * @param null $id
     * @return ArrayObject
     */
    public function transformGetRequest(array $input, array $json, array $resourceIds = [], $id = null): ArrayObject;

    /**
     * @param array $input
     * @param array $resourceIds
     * @param $id
     * @return ArrayObject
     */
    public function transformNoneGetRequest(array $input, array $resourceIds = [], $id = null): ArrayObject;

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