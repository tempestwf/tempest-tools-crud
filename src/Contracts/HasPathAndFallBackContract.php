<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/27/2017
 * Time: 5:19 PM
 */

namespace TempestTools\Crud\Contracts;


interface HasPathAndFallBackContract
{
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
}