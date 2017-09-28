<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/27/2017
 * Time: 5:19 PM
 */

namespace TempestTools\Crud\Contracts;


interface HasOptionsOverrideContract
{
    /**
     * @return array
     */
    public function getOptionsOverrides(): array;

    /**
     * @param array $optionsOverrides
     */
    public function setOptionsOverrides(array $optionsOverrides):void;
}