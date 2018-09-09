<?php

namespace OctoberFly\Map;

use Exception;
use Throwable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Foundation\Http\Events;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    /**
     * The application implementation.
     *
     * @var \OctoberFly\Map\Application
     */
    protected $app;

    protected $bootstrappers = [
        '\October\Rain\Foundation\Bootstrap\RegisterClassLoader',
        '\October\Rain\Foundation\Bootstrap\LoadEnvironmentVariables',

        // \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \OctoberFly\Map\Bootstrap\LoadConfiguration::class,

        '\October\Rain\Foundation\Bootstrap\LoadTranslation',

        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,

        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,

        '\October\Rain\Foundation\Bootstrap\RegisterOctober',

        // \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        // \Illuminate\Foundation\Bootstrap\BootProviders::class,
        \OctoberFly\Map\Bootstrap\RegisterAcrossProviders::class,
        \OctoberFly\Map\Bootstrap\OnWork::class,
        \OctoberFly\Map\Bootstrap\ResolveSomeFacadeAliases::class,
        \OctoberFly\Map\Bootstrap\CleanOnWorker::class,

    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // 'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        // 'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // 'can' => \Illuminate\Auth\Middleware\Authorize::class,
        // 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \October\Rain\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // \Illuminate\Auth\Middleware\Authenticate::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // \Illuminate\Auth\Middleware\Authorize::class,
    ];


    public function handle($request)
    {
        try {
            // moved to LaravelFlyServer::initAfterStart
            // $request::enableHttpMethodParameterOverride();

            $response = $this->sendRequestThroughRouter($request);

        } catch (Exception $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        } catch (Throwable $e) {

            $this->reportException($e = new FatalThrowableError($e));

            $response = $this->renderException($request, $e);
        }

        $this->app['events']->dispatch(
            new Events\RequestHandled($request, $response)
        );

        return $response;
    }

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        // moved to \OctoberFly\Map\Bootstrap\CleanOnWorker. After that, no need to clear in each request.
        // Facade::clearResolvedInstance('request');

        // replace $this->bootstrap();
        $this->app->bootInRequest();

        return (new Pipeline($this->app))
            ->send($request)
            // hack: Cache for kernel middlewares objects.
            // ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            ->through($this->app->shouldSkipMiddleware() ? [] : $this->getParsedKernelMiddlewares())
            ->then($this->dispatchToRouter());
    }

    /**
     * hack: Cache for kernel middlewares objects.
     * middlewars are frozened when the first request goes into Pipeline
     * @var array
     */
    static $parsedKernelMiddlewares = [];

    /**
     * hack: Cache for terminateMiddleware objects.
     * only kernel middlewares here
     * @var array
     */
    static $parsedTerminateMiddlewares = [];

    /**
     * @return array
     * hack: Cache for kernel middlewares objects.
     * hack: Cache for terminateMiddleware objects.
     */
    protected function getParsedKernelMiddlewares(): array
    {
        return static::$parsedKernelMiddlewares ?:
            (static::$parsedKernelMiddlewares = $this->app->parseKernelMiddlewares($this->middleware, static::$parsedTerminateMiddlewares));
    }

    /**
     * hack: Cache for terminateMiddleware objects.
     * including kernel middlewares and route middlewares
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\Response $response
     * @return void
     */
    protected function terminateMiddleware($request, $response)
    {
        $middlewares = $this->app->shouldSkipMiddleware() ? [] : array_merge(
            // hack
            // $this->gatherRouteMiddleware($request),
            $this->app->gatherRouteTerminateMiddleware($request),

            // $this->middleware
            static::$parsedTerminateMiddlewares
        );

        foreach ($middlewares as $middleware) {
            /**
             * hack: middlewares not only string, maybe objects now,
             */
            if (is_string($middleware)) {
                list($name) = $this->parseMiddleware($middleware);

                $instance = $this->app->make($name);

            } elseif (is_object($middleware)) {
                $instance = $middleware;
            } else {
                continue;
            }

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($request, $response);
            }
        }
    }


}
