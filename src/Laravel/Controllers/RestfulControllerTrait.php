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
use TempestTools\Crud\Laravel\Events\Controller\Init;
use TempestTools\Crud\Laravel\Events\Controller\PostDestroy;
use TempestTools\Crud\Laravel\Events\Controller\PostIndex;
use TempestTools\Crud\Laravel\Events\Controller\PostShow;
use TempestTools\Crud\Laravel\Events\Controller\PostStore;
use TempestTools\Crud\Laravel\Events\Controller\PostUpdate;
use TempestTools\Crud\Laravel\Events\Controller\PreDestroy;
use TempestTools\Crud\Laravel\Events\Controller\PreIndex;
use TempestTools\Crud\Laravel\Events\Controller\PreShow;
use TempestTools\Crud\Laravel\Events\Controller\PreStore;
use TempestTools\Crud\Laravel\Events\Controller\PreUpdate;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

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
     * Register the listeners for the subscriber.
     *
     * @param DispatcherContract|\Illuminate\Events\Dispatcher $events
     */
    public function subscribe(DispatcherContract $events):void
    {

        $events->listen(
            Init::class,
            static::class . '@Init'
        );

        $events->listen(
            PreIndex::class,
            static::class . '@PreIndex'
        );

        $events->listen(
            PostIndex::class,
            static::class . '@PostIndex'
        );

        $events->listen(
            PostStore::class,
            static::class . '@PostStore'
        );
        $events->listen(
            PreShow::class,
            static::class . '@PreShow'
        );
        $events->listen(
            PostShow::class,
            static::class . '@PostShow'
        );
        $events->listen(
            PreUpdate::class,
            static::class . '@PreUpdate'
        );
        $events->listen(
            PostUpdate::class,
            static::class . '@PostUpdate'
        );
        $events->listen(
            PreDestroy::class,
            static::class . '@PreDestroy'
        );
        $events->listen(
            PostDestroy::class,
            static::class . '@PostDestroy'
        );

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