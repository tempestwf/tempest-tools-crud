<?php

namespace TempestTools\Crud\Contracts\Orm\Helper;

use ArrayObject;
use Closure;
use RuntimeException;
use TempestTools\Common\Contracts\ArrayExpressionContract;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToAssociationPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\ArrayToFieldPropertyBuilderContract;
use TempestTools\Crud\Contracts\Orm\Builder\PrePersistEntityBuilderContract;
use TempestTools\Crud\Contracts\Orm\EntityContract;

interface EntityArrayHelperContract extends ArrayHelperContract
{

    /**
     * @param string $verb
     * @param string $property
     * @return string
     */
    public function accessorMethodName(string $verb, string $property): string;

    /**
     * Takes an array of objects, and tries to call extractValues on each one, it then stores the result on the objects
     * stored array.
     *
     * @param array $objects
     * @return ArrayObject
     * @throws \TempestTools\Common\Exceptions\Helper\ArrayHelperException
     */
    public function extract(array $objects): ArrayObject;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Automatically detects what operations should be run on a value and then runs them.
     *
     * @param mixed $value
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return array|ArrayObject|mixed|string
     * @throws \RuntimeException
     */
    public function parse($value, array $extra = [], $pathRequired = false, $parsePathResult = true);/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayExpressionContract $value
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return mixed
     */
    public function parseArrayExpression(ArrayExpressionContract $value, array $extra = [], $pathRequired = false, $parsePathResult = true);

    /**
     * Parses a closure and passes it $this
     *
     * @param Closure $closure
     * @param array $extra
     * @return mixed
     */
    public function parseClosure(Closure $closure, array $extra = []);

    /**
     * Looks at an array that has an "extends" key.
     * It calculates the full inheritance path by following the extends path set on the source that was passed,
     * that leads through the extends paths stored on the array attached to this class.
     * Once it has the full path of extends calculated, it starts using array_replace to apply the values from the
     * parts of the array referenced in the extends path. It then removes the extends property from the source array
     * and puts in instead a: extended property that lists the path that was used for extension.
     *
     * @param array $source
     * @return array
     * @throws \RuntimeException
     */
    public function parseInheritance(array $source): array;

    /**
     * Created the extends path used by parseInheritance. See that method for more details.
     * Example:
     * With an array like so:
     * *[
     *  'base'=> [
     *    'extends'=>[]
     *  ],
     *  'one'=> [
     *    'extends'=>[':base']
     *  ],
     *  'two'=> [
     *    'extends'=>[':one']
     *  ],
     *  'three'=> [
     *    'extends'=>[':two', ':four']
     *  ],
     *  'four'=> [
     *    'extends'=>[]
     *  ]
     *]
     * If you pass this method a source like so:
     * [
     *    'extends'=>[':two', ':four']
     *  ]
     * You would get a result of:
     * ["two", "one", "base", "four"]
     *
     * @param array $source
     * @return array
     * @throws \RuntimeException
     */
    public function parseInheritancePath(array $source): array;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Replaces placeholder values wrapped in {{}} with the paths stored inside them.
     * For instance
     * ?{{:key1:subKey1:subKey2}} would return "foo" from array:
     * [
     *  'key1' => [
     *      'subKey1' => [
     *          'subKey2' => 'foo'
     *      ]
     *  ]
     * ]
     *
     * @param string $template
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return string
     * @throws \RuntimeException
     */
    public function parseTemplate(string $template, array $extra = [], bool $pathRequired = false, bool $parsePathResult = true): string;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Gets an value from the array, using a : separated list of array keys passed in as a string. For instance
     * ?:key1:subKey1:subKey2 would return "foo" from array:
     * [
     *  'key1' => [
     *      'subKey1' => [
     *          'subKey2' => 'foo'
     *      ]
     *  ]
     * ]
     *
     * @param string $path
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return mixed
     * @throws \RuntimeException
     */
    public function parseStringPath(string $path, array $extra = [], bool $pathRequired = false, bool $parsePathResult = true);/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Gets an value from the array, using a array keys passed. For instance ['key1','subKey1','subKey2'] would return
     * "foo" from array:
     * [
     *  'key1' => [
     *      'subKey1' => [
     *          'subKey2' => 'foo'
     *      ]
     *  ]
     * ]
     *
     * @param array $path
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return mixed
     * @throws \RuntimeException
     */
    public function parseArrayPath(array $path, array $extra = [], bool $pathRequired = false, bool $parsePathResult = true);

    /**
     * @param ArrayObject $array
     * @return ArrayHelperContract
     */
    public function setArray(ArrayObject $array = null): ArrayHelperContract;

    /**
     * @return ArrayObject
     */
    public function getArray(): ArrayObject;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Finds the right most occurrence of a setting in the settings array, parses it if requested, and returns it.
     * If a key is passed it will look for that key in arrays passed to it.
     *
     * @param array $settings
     * @param string|null $key
     * @param bool $parse
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @return mixed
     * @throws \RuntimeException
     */
    public function findSetting(array $settings, string $key = null, bool $parse = true, array $extra = [], $pathRequired = false, $parsePathResult = true);

    /**
     * If the array passed is associative it wraps it in a numeric array
     *
     * @param array $array
     * @return array
     */
    public function wrapArray(array $array): array;

    /**
     * Checks if an array is numeric or not, if fast is set to false it will use a more through but slightly slower
     * method.
     *
     * @param array $array
     * @param bool $fast
     * @return bool
     */
    public function isNumeric(array $array, $fast = true): bool;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $values
     * @param array $enforce
     * @param array $extra
     * @param bool $pathRequired
     * @param bool $parsePathResult
     * @param bool $parse
     * @return bool
     * @throws \RuntimeException
     */
    public function testEnforceValues(array $values, array $enforce, array $extra = [], $pathRequired = false, $parsePathResult = true, $parse = true): bool;

    /**
     * @param string $fieldName
     * @param string $keyName
     * @return mixed
     * @throws RuntimeException
     */
    public function getConfigForField(string $fieldName, string $keyName);/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $chainType
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     */
    public function canChain(EntityContract $entity, string $associationName, string $chainType, bool $nosey = true): bool;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param string $assignType
     * @param array $fieldSettings
     * @param bool $nosey
     * @return bool
     * @throws \RuntimeException
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function canAssign(EntityContract $entity, string $associationName, string $assignType = null, array $fieldSettings = null, bool $nosey = true): bool;

    /**
     * @param EntityContract $entity
     * @param bool $nosey
     * @return bool
     * @throws \TempestTools\Crud\Exceptions\Orm\Helper\EntityArrayHelperException
     */
    public function allowed(EntityContract $entity, $nosey = true): bool;

    /**
     * @param string $fieldName
     * @param $params
     * @return array
     * @throws RuntimeException
     */
    public function getFieldSettings(string $fieldName, array $params = []): array;

    /**
     * @param EntityContract $entity
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function processSetField(EntityContract $entity, array $params);

    /**
     * @param EntityContract $entity
     * @param string $fieldName
     * @param $value
     * @throws \RuntimeException
     */
    public function setField(EntityContract $entity, string $fieldName, $value): void;

    /**
     * @param EntityContract $entity
     * @param string $associationName
     * @param array $values
     * @return array
     * @throws \RuntimeException
     */
    public function processAssociationParams(EntityContract $entity, string $associationName, array $values): array;/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $assignType
     * @param string $associationName
     * @param EntityContract $entity
     * @param bool $force
     * @throws \RuntimeException
     */
    public function bindAssociation(EntityContract $entity, string $assignType = null, string $associationName, $force = false): void;

    /**
     * On an entity with HasLifecycleCallbacks it will run the special features of tt entities before persist
     *
     * @param EntityContract $entity
     * @throws \RuntimeException
     */
    public function ttPrePersist(EntityContract $entity): void;

    /**
     * @param EntityContract $entity
     * @param array $fields
     * @return array
     */
    public function getValuesOfFields(EntityContract $entity, array $fields = []): array;

    /**
     * @return ArrayToFieldPropertyBuilderContract
     */
    public function getArrayToFieldPropertyBuilder(): ArrayToFieldPropertyBuilderContract;

    /**
     * @param ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder
     */
    public function setArrayToFieldPropertyBuilder(ArrayToFieldPropertyBuilderContract $arrayToFieldPropertyBuilder);

    /**
     * @return ArrayToAssociationPropertyBuilderContract
     */
    public function getArrayToAssociationPropertyBuilder(): ArrayToAssociationPropertyBuilderContract;

    /**
     * @param ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder
     */
    public function setArrayToAssociationPropertyBuilder(ArrayToAssociationPropertyBuilderContract $arrayToAssociationPropertyBuilder);

    /**
     * @return PrePersistEntityBuilderContract
     */
    public function getPrePersistEntityBuilder(): PrePersistEntityBuilderContract;

    /**
     * @param PrePersistEntityBuilderContract $prePersistEntityBuilder
     */
    public function setPrePersistEntityBuilder(PrePersistEntityBuilderContract $prePersistEntityBuilder);

}