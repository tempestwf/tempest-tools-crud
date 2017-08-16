<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/15/2017
 * Time: 6:12 PM
 */

namespace TempestTools\Crud\Contracts;

use \Exception;

use TempestTools\Common\Contracts\ArrayHelperContract;



interface RepositoryContract
{

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init( ArrayHelperContract $arrayHelper = NULL, array $path=NULL, array $fallBack=NULL, bool $force= true);
    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    public function read (array $params=[], array $frontEndOptions=[], array $optionOverrides = []):array;


    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function create(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function update(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

    /**
     * @param array $params
     * @param array $optionOverrides
     * @param array $frontEndOptions
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function delete(array $params, array $optionOverrides = [], array $frontEndOptions=[]):array;

    /**
     * @param string $fieldName
     * @param array $values
     * @return mixed
     */
    public function findIn(string $fieldName, array $values);

    public function getTTConfig(): array;
    /**
     * @return array|NULL
     */
    public function getOptions(): ?array;
    /**
     * @param array|NULL $options
     */
    public function setOptions($options):void;
    /**
     * @return null|DataBindHelperContract
     */
    public function getDataBindHelper(): ?DataBindHelperContract;
    /**
     * @param DataBindHelperContract $dataBindHelper
     */
    public function setDataBindHelper(DataBindHelperContract $dataBindHelper):void;


    /**
     * @return NULL|QueryBuilderHelperContract
     */
    public function getConfigArrayHelper():?QueryBuilderHelperContract;
    /**
     * @param QueryBuilderHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(QueryBuilderHelperContract $configArrayHelper):void;
}