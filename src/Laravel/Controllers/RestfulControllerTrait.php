<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/22/2017
 * Time: 5:45 PM
 */

namespace TempestTools\Scribe\Laravel\Controllers;
use ArrayObject;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TempestTools\Common\Contracts\ArrayHelperContract;
use TempestTools\Common\Contracts\Doctrine\Transformers\SimpleTransformerContract;
use TempestTools\Common\Utility\TTConfigTrait;
use TempestTools\Scribe\Constants\ControllerEventsConstants;
use TempestTools\Scribe\Contracts\Controller\Helper\ControllerArrayHelperContract;
use TempestTools\Scribe\Contracts\Orm\RepositoryContract;
use TempestTools\Scribe\Controller\Helper\ControllerArrayHelper;
use TempestTools\Scribe\Exceptions\Laravel\Controller\ControllerException;
use TempestTools\Scribe\Laravel\Events\Controller\Init;
use TempestTools\Scribe\Laravel\Events\Controller\PostDestroy;
use TempestTools\Scribe\Laravel\Events\Controller\PostIndex;
use TempestTools\Scribe\Laravel\Events\Controller\PostShow;
use TempestTools\Scribe\Laravel\Events\Controller\PostStore;
use TempestTools\Scribe\Laravel\Events\Controller\PostUpdate;
use TempestTools\Scribe\Laravel\Events\Controller\PreDestroy;
use TempestTools\Scribe\Laravel\Events\Controller\PreIndex;
use TempestTools\Scribe\Laravel\Events\Controller\PreShow;
use TempestTools\Scribe\Laravel\Events\Controller\PreStore;
use TempestTools\Scribe\Laravel\Events\Controller\PreUpdate;
use Illuminate\Events\Dispatcher;
/**
 * A trait that can be applied to a controller to facilitate the packages functionality.
 * @link    https://github.com/tempestwf
 * @author  William Tempest Wright Ferrer <https://github.com/tempestwf>
 */
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

    /** @var array  */
    protected /** @noinspection PropertyCanBeStaticInspection */ $availableModes = ['GET', 'POST', 'PUT', 'DELETE'];

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Initializes the controller setting up it's array helpers and relevant data based on it's config context.
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
     * Initializes the controller array helper
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
     * @return JsonResponse
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function index(Request $request): JsonResponse
    {

        try {
            $this->getConfigArrayHelper()->checkAllowed('index');
            $settings = $this->getConfigArrayHelper()->transformGetRequest($request->input(), $request->json()->all(), $request->route()->parameters());
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPathNoMode(), $this->getTTFallBackNoMode());
            $this->getConfigArrayHelper()->start();
            event(new PreIndex($settings));
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
     * @return JsonResponse
     * @throws \TempestTools\Scribe\Exceptions\Laravel\Controller\ControllerException
     */
    public function create(): JsonResponse
    {
        throw ControllerException::methodNotImplemented('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->getConfigArrayHelper()->checkAllowed('store');
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input(), $request->route()->parameters());
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPathNoMode(), $this->getTTFallBackNoMode());
            $this->getConfigArrayHelper()->start();
            event(new PreStore($settings));
            $isNumeric = $this->getArrayHelper()->isNumeric($settings['params']);
            $result = $repo->create($settings['params'], $settings['overrides'], $settings['frontEndOptions']);
            $transformerSettings = $this->getTransformerSettings($settings);
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            $settings['result'] = $isNumeric?$settings['result']:$settings['result'][0];
            event(new PostStore($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }
        return response()->json($settings['result'],JsonResponse::HTTP_CREATED);
    }


    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \LogicException
     */
    public function show(Request $request, $id=null): JsonResponse
    {
        try {
            $this->getConfigArrayHelper()->checkAllowed('show');
            $settings = $this->getConfigArrayHelper()->transformGetRequest($request->input(), $request->json()->all(), $request->route()->parameters(), $id);
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPathNoMode(), $this->getTTFallBackNoMode());
            $this->getConfigArrayHelper()->start();
            event(new PreShow($settings));
            $result = $repo->read($settings['query'], $settings['frontEndOptions'], $settings['overrides']);
            if (count($result['result']) === 0) {
                throw new ResourceException('Error: your requested resource does not exist, or you do not have access to it.');
            }
            $settings['result'] = $result['result'][0];
            event(new PostShow($settings));
        } catch (Exception $e) {
            $this->getConfigArrayHelper()->stop(true);
            throw $e;
        }

        return response()->json($settings['result']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws \TempestTools\Scribe\Exceptions\Laravel\Controller\ControllerException
     */
    public function edit(): JsonResponse
    {
        throw ControllerException::methodNotImplemented('edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \LogicException
     */
    public function update(Request $request, $id=null): JsonResponse
    {
        try {
            $this->getConfigArrayHelper()->checkAllowed('update');
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input(), $request->route()->parameters(), $id);
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPathNoMode(), $this->getTTFallBackNoMode());
            $this->getConfigArrayHelper()->start();
            event(new PreUpdate($settings));
            $result = $repo->update($settings['params'], $settings['overrides'], $settings['frontEndOptions']);

            //$arrayCopy = $this->getArrayHelper()->getArray()->getArrayCopy();
            if ($id !== 'batch' && count($result) === 0) {
                throw new ResourceException('Error: the resource you attempted to update does not exist, or you do not have access to it.');
            }
            $transformerSettings = $this->getTransformerSettings($settings);
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            $settings['result'] = $id === 'batch'?$settings['result']:$settings['result'][0];
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
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function destroy(Request $request, $id = null): JsonResponse
    {
        try {
            $this->getConfigArrayHelper()->checkAllowed('destroy');
            $settings = $this->getConfigArrayHelper()->transformNoneGetRequest($request->input(), $request->route()->parameters(), $id);
            $repo = $this->getRepo();
            $repo->init($this->getArrayHelper(), $this->getTTPathNoMode(), $this->getTTFallBackNoMode());
            $this->getConfigArrayHelper()->start();
            event(new PreDestroy($settings));
            $result = $repo->delete($settings['params'], $settings['overrides'], $settings['frontEndOptions']);
            $transformerSettings = $this->getTransformerSettings($settings);
            if ($id !== 'batch' && \count($result) === 0) {
                throw new ResourceException('Error: the resource you attempted to destroy does not exist, or you do not have access to it.');
            }
            $settings['result'] = $this->getTransformer()->setSettings($transformerSettings)->transform($result);
            $settings['result'] = $id === 'batch'?$settings['result']:$settings['result'][0];
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
     * @param Dispatcher |\Illuminate\Events\Dispatcher $events
     */
    public function subscribe(Dispatcher $events):void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $eventsInfo = ControllerEventsConstants::getAll();

        foreach ($eventsInfo as $eventInfo) {
            if (method_exists($this, $eventInfo['on'])) {
                $listeners = $events->getListeners($eventInfo['class']);
                if (\count($listeners) === 0)
                {
                    $events->listen(
                        $eventInfo['class'],
                        static::class . '@' . $eventInfo['on']
                    );
                }
            }
        }
    }

    /**
     * Gets the settings to pass to the simple transformer
     * @param ArrayObject $settings
     * @return array
     */
    protected function getTransformerSettings(ArrayObject $settings):array {
        $transformerSettings = $settings['controllerOptions']['transformerSettings'] ?? [];
        $transformerSettings['frontEndOptions'] = $transformerSettings['frontEndOptions'] ?? [];
        $transformerSettings['frontEndOptions'] = array_replace_recursive($transformerSettings['frontEndOptions'], $settings['frontEndOptions']);
        return $transformerSettings;
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

    /**
     * @return array
     */
    abstract public function getTTConfig(): array;

    /**
     * @return array
     */
    public function getAvailableModes(): array
    {
        return $this->availableModes;
    }
}