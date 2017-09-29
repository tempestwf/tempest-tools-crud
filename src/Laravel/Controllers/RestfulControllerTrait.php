<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 5:45 PM
 */

namespace TempestTools\Crud\Laravel\Controllers;
use ArrayObject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Contracts\Doctrine\Transformers\SimpleTransformerContract;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Crud\Contracts\Orm\RepositoryContract;
use TempestTools\Crud\Controller\Helper\ControllerArrayHelper;
use TempestTools\Crud\Controller\Helper\ControllerArrayHelperContract;
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
    use TTConfigTrait;
    /** @var RepositoryContract $repo */
    protected $repo;

    /** @var SimpleTransformerContract $transformer*/
    protected $transformer;

    /** @var array $overrides */
    protected $overrides = [];
    /**
     * @var string|null $lastMode
     */
    protected $lastMode;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $mode
     * @param ArrayHelperContract|null $arrayHelper
     * @param array|null $path
     * @param array|null $fallBack
     * @param bool $force
     * @throws \RuntimeException
     */
    public function init(string $mode, ArrayHelperContract $arrayHelper = null, array $path = null, array $fallBack = null, bool $force = false):void
    {
        $settings = new ArrayObject(['self'=>$this, 'mode'=>$mode, 'arrayHelper'=>$arrayHelper, 'path'=>$path, 'fallBack'=>$fallBack, 'force'=>$force]);
        event(new Init($settings));
        $force = $this->coreInit($settings['arrayHelper'], $settings['path'], $settings['fallBack'], $settings['force'], $settings['mode']);
        $this->controllerArrayHelperInit($force, $settings['mode']);
        $this->setLastMode($settings['mode']);
    }

    /**
     * @param bool $force
     * @param string $mode
     * @throws \RuntimeException
     */
    protected function controllerArrayHelperInit(bool $force = false, string $mode):void
    {
        if ($force === true || $this->getConfigArrayHelper() === null || $mode !== $this->getLastMode()) {
            $controllerArrayHelper = new ControllerArrayHelper(null, $this);
            $this->parseTTConfig($controllerArrayHelper);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function index(Request $request): Response
    {

        try {
            $settings = $this->getConfigArrayHelper()->transformGetRequest($request->input(), $request->json());
            event(new PreIndex($settings));
            $this->getConfigArrayHelper()->start();
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
            $result = $repo->read($settings['query'], $settings['frontEndOptions'], $settings['overrides']);
            $settings['result'] = $result;
            event(new PostIndex($settings));
            $this->getConfigArrayHelper()->stop();
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }

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
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function store(Request $request): Response
    {
        try {
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input());
            event(new PreStore($settings));
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
            $result = $repo->create($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
            $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            event(new PostStore($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }
        return response()->json($settings['result']);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function show(Request $request, $id=null): Response
    {
        try {
            $settings = $this->getConfigArrayHelper()->transformGetRequest($request->input(), $request->json(), $id);
            event(new PreIndex($settings));
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
            $result = $repo->read($settings['query'], $settings['frontEndOptions'], $settings['overrides']);
            $settings['result'] = $result;
            event(new PostIndex($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }

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
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function update(Request $request, $id=null): Response
    {
        try {
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input(), $id);
            event(new PreUpdate($settings));
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
            $result = $repo->update($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
            $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            event(new PostUpdate($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }
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
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function destroy(Request $request, $id = null): Response
    {
        try {
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input(), $id);
            event(new PreDestroy($settings));
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPath(), $this->getTTFallBack());
            $result = $repo->update($settings['params'], $settings['frontEndOptions'], $settings['overrides']);
            $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            event(new PostDestroy($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }
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
    public function getOverrides(): array
    {
        return $this->overrides;
    }

    /**
     * @param array $overrides
     */
    public function setOverrides(array $overrides):void
    {
        $this->overrides = $overrides;
    }

    /**
     * @param null|string $lastMode
     */
    public function setLastMode(string $lastMode = null):void
    {
        $this->lastMode = $lastMode;
    }

    /**
     * @return NULL|ControllerArrayHelperContract
     */
    public function getConfigArrayHelper():?ControllerArrayHelperContract
    {
        return $this->configArrayHelper;
    }

    /**
     * @param ControllerArrayHelperContract $configArrayHelper
     */
    public function setConfigArrayHelper(ControllerArrayHelperContract $configArrayHelper):void
    {
        $this->configArrayHelper = $configArrayHelper;
    }

}