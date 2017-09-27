<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 5:45 PM
 */

namespace TempestTools\Crud\Laravel\Controllers;
use ArrayObject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Contracts\Doctrine\Transformers\SimpleTransformerContract;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Exceptions\Laravel\Controller\ControllerException;
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

    /** @var array $options */
    protected $options = [];

    /** @var array $optionsOverrides */
    protected $optionsOverrides = [];

    /** @var array $path */
    protected $path;

    /** @var array $fallback */
    protected $fallback;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function index(Request $request): Response
    {
        $settings = $this->transformGetRequest($request, 'index');
        event(new Init($settings));
        event(new PreIndex($settings));
        $repo = $this->getRepo();
        $repo->init($this->getArrayHelper(), $this->getPath(), $this->getFallback());
        $result = $repo->read($settings['query'], $settings['frontEndOptions'], $settings['overrides']);
        $settings['result'] = $result;
        event(new PostIndex($settings));
        return response()->json($settings['result']);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     * @throws \TempestTools\Crud\Exceptions\Laravel\Controller\ControllerException
     */
    public function create(): Response
    {
        throw ControllerException::prePersistValidatorFails('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function store(Request $request): Response
    {
        $settings = $this->transformNoneGetRequest($request, 'store');
        event(new Init($settings));
        event(new PreStore($settings));
        $repo = $this->getRepo();
        $repo->init($this->getArrayHelper(), $this->getPath(), $this->getFallback());
        $result = $repo->create($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
        $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
        $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
        event(new PostStore($settings));
        return response()->json($settings['result']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function show($id, Request $request): Response
    {
        $settings = $this->transformGetRequest($request, 'show', $id);
        event(new Init($settings));
        event(new PreIndex($settings));
        $repo = $this->getRepo();
        $repo->init($this->getArrayHelper(), $this->getPath(), $this->getFallback());
        $result = $repo->read($settings['query'], $settings['frontEndOptions'], $settings['overrides']);
        $settings['result'] = $result;
        event(new PostIndex($settings));
        return response()->json($settings['result']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws \TempestTools\Crud\Exceptions\Laravel\Controller\ControllerException
     */
    public function edit(): Response
    {
        throw ControllerException::prePersistValidatorFails('edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return Response
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function update(Request $request, $id=null): Response
    {
        $settings = $this->transformNoneGetRequest($request, 'update', $id);
        event(new Init($settings));
        event(new PreUpdate($settings));
        $repo = $this->getRepo();
        $repo->init($this->getArrayHelper(), $this->getPath(), $this->getFallback());
        $result = $repo->update($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
        $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
        $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
        event(new PostUpdate($settings));
        return response()->json($settings['result']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function destroy(Request $request, $id = null): Response
    {
        $settings = $this->transformNoneGetRequest($request, 'destroy', $id);
        event(new Init($settings));
        event(new PreDestroy($settings));
        $repo = $this->getRepo();
        $repo->init($this->getArrayHelper(), $this->getPath(), $this->getFallback());
        $result = $repo->update($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
        $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
        $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
        event(new PostDestroy($settings));
        return response()->json($settings['result']);
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
            PreStore::class,
            static::class . '@PreStore'
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

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options):void
    {
        $this->options = $options;
    }


    /**
     * @param Request $request
     * @param string $key
     * @param null $id
     * @return ArrayObject
     * @throws \LogicException
     */
    protected function transformGetRequest (Request $request, string $key, $id = null):ArrayObject
    {
        $input = $request->input();
        $queryLocation = $input['queryLocation'] ?? 'params';
        $query = [];
        $options = [];
        switch ($queryLocation) {
            case 'body':
                $params = json_decode($request->getContent(), true);
                $query = $params['query'];
                $options = $params['options'];
                break;
            case 'singleParam':
                $params = json_decode($request->getContent(),  true);
                $query = $params['query'];
                $options = $params['options'];
                break;
            case 'params':
                $query = $input;
                unset($query['queryLocation']);
                $options = ['useGetParams'=>true];
                break;
        }
        $controllerOptions = array_replace_recursive($this->getOptions(), $this->getOptionsOverrides())[$key]??[];
        $overrides = $options['overrides'] ?? [];

        if ($id !== null) {
            $alias = $controllerOptions['alias'] ?? $this->getRepo()->getEntityAlias();
            $query = [
                'where'=>[
                    [
                        'field'=>$alias . '.id',
                        'type'=>'and',
                        'operator'=>'eq',
                        'arguments'=>[$id]
                    ],
                ]
            ];
        }

        return new ArrayObject(['query'=>$query, 'frontEntOptions'=>$options, 'controllerOptions'=>$controllerOptions, 'overrides'=>$overrides, 'request'=>$request, 'controller'=>$this]);
    }


    /**
     * @param Request $request
     * @param string $key
     * @param $id
     * @return ArrayObject
     */
    protected function transformNoneGetRequest (Request $request, string $key, $id = null):ArrayObject
    {
        $input = $request->input();
        $params = $input['params'] ?? [];
        $options = $input['options'] ?? [];
        $controllerOptions = array_replace_recursive($this->getOptions(), $this->getOptionsOverrides())[$key]??[];
        $overrides = $options['overrides'] ?? [];
        if ($id !== null ) {
            $params = [$id=>$params];
        }
        return new ArrayObject(['params'=>$params, 'frontEntOptions'=>$options, 'overrides'=>$overrides, 'controllerOptions'=>$controllerOptions, 'request'=>$request, 'controller'=>$this]);
    }

    /**
     * @return array
     */
    public function getOptionsOverrides(): array
    {
        return $this->optionsOverrides;
    }

    /**
     * @param array $optionsOverrides
     */
    public function setOptionsOverrides(array $optionsOverrides):void
    {
        $this->optionsOverrides = $optionsOverrides;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param array $path
     */
    public function setPath(array $path):void
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getFallback(): array
    {
        return $this->fallback;
    }

    /**
     * @param array $fallback
     */
    public function setFallback(array $fallback):void
    {
        $this->fallback = $fallback;
    }

    /**
     * @return null|ArrayHelperContract
     */
    abstract public function getArrayHelper():?ArrayHelperContract;

}