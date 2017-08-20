<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 6:37 PM
 */

namespace TempestTools\Crud\Orm\Utility;


use TempestTools\Crud\Contracts\Orm\RepositoryContract;

trait RepositoryTrait
{
    /**
     * @var \TempestTools\Crud\Contracts\Orm\RepositoryContract $repository
     */
    protected $repository;

    /**
     * @return \TempestTools\Crud\Contracts\Orm\RepositoryContract
     */
    public function getRepository(): RepositoryContract
    {
        return $this->repository;
    }

    /**
     * @param RepositoryContract $repository
     */
    public function setRepository(RepositoryContract $repository):void
    {
        $this->repository = $repository;
    }


}