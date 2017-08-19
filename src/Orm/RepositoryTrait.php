<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/18/2017
 * Time: 6:37 PM
 */

namespace TempestTools\Crud\Orm;


use TempestTools\Crud\Contracts\RepositoryContract;

trait RepositoryTrait
{
    /**
     * @var RepositoryContract $repository
     */
    protected $repository;

    /**
     * @return RepositoryContract
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