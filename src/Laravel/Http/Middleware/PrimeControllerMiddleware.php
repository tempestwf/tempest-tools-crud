<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 9/27/2017
 * Time: 5:14 PM
 */

namespace TempestTools\Crud\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TempestTools\Common\ArrayObject\DefaultTTArrayObject;
use TempestTools\Common\Exceptions\Laravel\Http\Middleware\CommonMiddlewareException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Crud\Contracts\Controller\ControllerContract;



class PrimeControllerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \RuntimeException
     */
    public function handle(Request $request, Closure $next)
    {
        $controller = $request->route()->getController();

        if ($controller instanceof ControllerContract === false) {
            throw CommonMiddlewareException::controllerDoesNotImplement('ControllerContract');
        }

        /** @var  ControllerContract $controller */
        $arrayHelper = $controller->getArrayHelper() ?? new ArrayHelper(new DefaultTTArrayObject());

        $actions = $request->route()->getAction();
        $ttPath = $actions['ttPath'] ?? ['default'];
        $ttFallBack = $actions['ttFallback'] ?? ['default'];
        $configOverrides = $actions['configOverrides'] ?? [];
        $controller->setOverrides($configOverrides);
        $controller->init($request->getMethod(), $arrayHelper, $ttPath, $ttFallBack);

        return $next($request);
    }
}