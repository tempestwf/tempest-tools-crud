<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/22/2017
 * Time: 5:15 PM
 */

namespace TempestTools\Crud\Contracts\Orm\Builder;

use TempestTools\Common\Contracts\ArrayHelperContract;

/**
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
interface ArrayToAssociationPropertyBuilderContract
{
    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function enforce(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param mixed $fieldSetting
     * @return mixed
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     * @internal param EntityContract $entity
     */
    public function closure(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param $params
     * @param array $values
     * @param mixed $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function setTo(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting);

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ArrayHelperContract $arrayHelper
     * @param string $fieldName
     * @param array $values
     * @param array $params
     * @param $fieldSetting
     * @return mixed
     * @internal param EntityContract $entity
     */
    public function mutate(ArrayHelperContract $arrayHelper, string $fieldName, array $values, array $params, $fieldSetting);

    /**
     * @param $name
     * @param $arguments
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function __call($name, $arguments):void;
}