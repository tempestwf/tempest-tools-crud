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
use TempestTools\Common\Contracts\HasArrayHelperContract;
use TempestTools\Common\Exceptions\Laravel\Http\Middleware\CommonMiddlewareException;
use TempestTools\Common\Helper\ArrayHelper;
use TempestTools\Crud\Contracts\HasOptionsOverrideContract;
use TempestTools\Crud\Contracts\HasPathAndFallBackContract;


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

        if ($controller instanceof HasArrayHelperContract === false) {
            throw CommonMiddlewareException::controllerDoesNotImplement('HasArrayHelperContract');
        }

        if ($controller instanceof HasPathAndFallBackContract === false) {
            throw CommonMiddlewareException::controllerDoesNotImplement('HasPathAndFallBackContract');
        }

        if ($controller instanceof HasOptionsOverrideContract === false) {
            throw CommonMiddlewareException::controllerDoesNotImplement('HasOptionsOverrideContract');
        }

        $arrayHelper = $controller->getArrayHelper() ?? new ArrayHelper();
        $controller->setArrayHelper($arrayHelper);

        $actions = $request->route()->getAction();
        $ttPath = $actions['ttPath'] ?? ['default'];
        $ttFallBack = $actions['ttFallBack'] ?? ['default'];
        $optionsOverrides = $actions['optionsOverrides'] ?? [];
        /** @var  HasPathAndFallBackContract $controller */
        $controller->setTTPath($ttPath);
        $controller->setTTPath($ttFallBack);

        /** @var HasOptionsOverrideContract $controller */
        $controller->setOptionsOverrides($optionsOverrides);

        return $next($request);
    }
}