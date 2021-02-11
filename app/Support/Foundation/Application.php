<?php

namespace Plugin\Support\Foundation;

use Plugin\Support\Auth\AuthServiceProvider;
use Plugin\Support\Container\Container;
use Plugin\Support\Events\EventServiceProvider;
use Plugin\Support\Foundation\Exceptions\Handler as ExceptionHandler;
use Plugin\Support\Foundation\Providers\ConsoleServiceProvider;
use Plugin\Support\Filesystem\FilesystemServiceProvider;
use Plugin\Support\Http\Request;
use Plugin\Support\Routing\Pipeline;
use Plugin\Support\View\ViewServiceProvider;
use RuntimeException;
use Throwable;

class Application extends Container
{
    /**
     * The base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The registered providers
     *
     * @var array
     */
    protected $providers = [];

    /**
     * The application namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * The global middleware
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The route middleware
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * Create the applicaiton
     *
     * @param string $basePath
     */
    public function __construct(string $basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->registerBaseBindings();
        $this->registerCoreProviders();
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);

        $this->instance('env', wp_get_environment_type());

        $this->singleton(ExceptionHandler::class, function ($app) {
            return new ExceptionHandler($app);
        });
    }

    /**
     * Register the core service providers
     *
     * @return void
     */
    protected function registerCoreProviders()
    {
        $this->register(AuthServiceProvider::class);
        $this->register(EventServiceProvider::class);
        $this->register(FilesystemServiceProvider::class);
        $this->register(ViewServiceProvider::class);

        if ($this->runningInConsole()) {
            $this->register(ConsoleServiceProvider::class);
        }
    }

    /**
     * Get the app base path
     *
     * @return string
     */
    public function basePath(string $path = null)
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Register a service provider
     *
     * @param string $provider
     * @return void
     */
    public function register(string $provider)
    {
        $provider = new $provider($this);

        $provider->register();

        $this->providers[] = $provider;
    }

    /**
     * Boot the service providers
     *
     * @return void
     */
    protected function bootProviders()
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }

    /**
     * Capture the server request
     *
     * @return \Plugin\Support\Http\Request
     */
    protected function captureRequest()
    {
        $this->instance('request', $request = Request::capture());

        return $request;
    }

    /**
     * Capture the server request
     *
     * @return void
     */
    protected function runRequestThroughStack(Request $request)
    {
        try {
            $response = (new Pipeline())
                ->send($request)
                ->through($this->middleware)
                ->then(function ($request) {
                    $this->instance('request', $request);

                    return $request;
                });
        } catch (Throwable $e) {
            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * Add a global middleware to the app
     *
     * @param array $middleware
     * @return self
     */
    public function middleware(array $middleware)
    {
        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Add a global middleware to the app
     *
     * @param array $middleware
     * @return self
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * Get the route middleware
     *
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }

    /**
     * Boot the application
     *
     * @return void
     */
    public function boot()
    {
        $request = $this->captureRequest();

        $this->runRequestThroughStack($request);

        $this->bootProviders();
    }

    /**
     * Get the app namespace
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if ($this->namespace) {
            return $this->namespace;
        }

        $composer = json_decode($this['files']->get($this->basePath('composer.json')), true);

        if ($composer['autoload'] && $loader = $composer['autoload']['psr-4']) {
            foreach ($loader as $namespace => $path) {
                if (realpath($this->basePath('app')) === realpath($this->basePath($path))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * Determine if the app is running in the console
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return class_exists('WP_CLI');
    }

    /**
     * Render an HTTP exception
     *
     * @param \Plugin\Support\Http\Request $request
     * @param \Throwable $e
     * @return string
     */
    public function renderException(Request $request, Throwable $e)
    {
        return $this[ExceptionHandler::class]->render($request, $e);
    }

    /**
     * Get or check the current application environment.
     *
     * @param  string|array|null  $environments
     * @return string|bool
     */
    public function environment($environments = null)
    {
        if ($environments) {
            return in_array($this['env'], (array) $environments);
        }

        return $this['env'];
    }

    /**
     * Determine if the app is in a local environment
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this['env'] == 'local';
    }

    /**
     * Determine if the app is in a development environment
     *
     * @return bool
     */
    public function isDevelopment()
    {
        return $this['env'] == 'development';
    }

    /**
     * Determine if the app is in a staging environment
     *
     * @return bool
     */
    public function isStaging()
    {
        return $this['env'] == 'staging';
    }

    /**
     * Determine if the app is in a production environment
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this['env'] == 'production';
    }
}
