<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/28/2017
 * Time: 6:22 PM
 */

namespace TempestTools\Scribe\Contracts\Controller;


use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Contracts\Doctrine\Transformers\SimpleTransformerContract;
use TempestTools\Scribe\Contracts\HasTTConfig;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface ControllerContract extends HasTTConfig
{

    /**
     * @return RepositoryContract
     */
    public function getRepo(): RepositoryContract;
    /**
     * @param RepositoryContract $repo
     */
    public function setRepo(RepositoryContract $repo):void;

    /**
     * @return SimpleTransformerContract
     */
    public function getTransformer(): SimpleTransformerContract;
    /**
     * @param SimpleTransformerContract $transformer
     */
    public function setTransformer(SimpleTransformerContract $transformer):void;
    /**
     * @return array
     */
    public function getOverrides(): array;

    /**
     * @param array $overrides
     */
    public function setOverrides(array $overrides):void;

    /**
     * @return null|ArrayHelperContract
     */
    public function getArrayHelper():?ArrayHelperContract;

    /**
     * @param null|string $lastMode
     */
    public function setLastMode(string $lastMode = null):void;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false):void;

}