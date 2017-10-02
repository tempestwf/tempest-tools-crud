<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/27/2017
 * Time: 5:19 PM
 */

namespace TempestTools\Crud\Contracts;


use TempestTools\Common\Contracts\ArrayHelperContract;

interface HasTTConfig
{

    /**
     * @return array
     */
    public function getTTConfig(): array;

    /**
     * @param array $ttPath
     */
    public function setTTPath(array $ttPath): void;

    /**
     * @param array $ttFallBack
     */
    public function setTTFallBack(array $ttFallBack): void;
    /**
     * @return NULL|array
     */
    public function getTTPath(): ?array;
    /**
     * @return NULL|array
     */
    public function getTTFallBack(): ?array;
    /**
     * @return NULL|ArrayHelperContract
     */
    //public function getConfigArrayHelper():?ArrayHelperContract;

    /**
     * @param ArrayHelperContract $configArrayHelper
     */
    //public function setConfigArrayHelper(ArrayHelperContract $configArrayHelper): void;

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Common logic for checking if the permissive settings allow something to be don
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $canDo
     * @param string $target
     * @return bool
     */
    public function permissivePermissionCheck ($high, array $low, string $canDo, string $target):bool;

    /**
     * @param array|\ArrayObject $high
     * @param array $low
     * @param string $setting
     * @return bool|mixed|null
     */
    public function highLowSettingCheck($high, array $low = NULL, string $setting);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Common logic for checking if the permissive settings allow something to be don
     * @param array|\ArrayObject $high
     * @param array $low
     * @return bool
     */
    public function permissiveAllowedCheck ($high, array $low):bool;
}