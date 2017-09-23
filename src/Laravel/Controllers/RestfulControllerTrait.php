<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 5:45 PM
 */

namespace TempestTools\Crud\Laravel\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TempestTools\Common\Contracts\Doctrine\Transformers\SimpleTransformerContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;

trait RestfulControllerTrait
{
    /** @var RepositoryContract $repo */
    protected $repo;

    /** @var SimpleTransformerContract $transformer*/
    protected $transformer;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $bob = 'your uncle';

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id): Response
    {
        //
    }

    /**
     * @return RepositoryContract
     */
    public function getRepo(): RepositoryContract
    {
        return $this->repo;
    }

    /**
     * @param RepositoryContract $repo
     */
    public function setRepo(RepositoryContract $repo):void
    {
        $this->repo = $repo;
    }

    /**
     * @return SimpleTransformerContract
     */
    public function getTransformer(): SimpleTransformerContract
    {
        return $this->transformer;
    }

    /**
     * @param SimpleTransformerContract $transformer
     */
    public function setTransformer(SimpleTransformerContract $transformer):void
    {
        $this->transformer = $transformer;
    }
}