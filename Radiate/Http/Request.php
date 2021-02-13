<?php

namespace Radiate\Http;

use ArrayAccess;
use Closure;
use JsonSerializable;

class Request implements ArrayAccess, JsonSerializable
{
    /**
     * The request attributes
     *
     * @var array
     */
    protected $request;

    /**
     * The cookie attributes
     *
     * @var array
     */
    protected $cookies;

    /**
     * The file attributes
     *
     * @var array
     */
    protected $files;

    /**
     * The server attributes
     *
     * @var array
     */
    protected $server;

    /**
     * The headers attributes
     *
     * @var array
     */
    protected $headers;

    /**
     * The user resolver
     *
     * @var \Closure
     */
    protected $userResolver;

    /**
     * Create the request instance
     *
     * @param array $request
     * @param array $cookies
     * @param array $files
     * @param array $server
     */
    public function __construct(array $request = [], array $cookies = [], array $files = [], array $server = [])
    {
        $this->request = $request;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->headers = $this->getHeaders($server);
    }

    /**
     * Capture the global request
     *
     * @return \Radiate\Http\Request
     */
    public static function capture()
    {
        return new static($_REQUEST, $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * Get the headers from the server global
     *
     * @param array $server
     * @return array
     */
    protected function getHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get a server attribute
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get a header
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Determine if the method matches the given method
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) == $this->method();
    }

    /**
     * Get the intended method
     *
     * @return string
     */
    public function method()
    {
        return strtoupper($this->get('_method', $this->realMethod()));
    }

    /**
     * Get the real request method
     *
     * @return string
     */
    public function realMethod()
    {
        return strtoupper($this->server('REQUEST_METHOD', 'GET'));
    }

    /**
     * Merge the attributes into the request
     *
     * @param array $attributes
     * @return self
     */
    public function merge(array $attributes)
    {
        $this->request = array_merge($this->request, $attributes);

        return $this;
    }

    /**
     * Determine if the request was made with AJAX
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->header('X_REQUESTED_WITH') == 'XMLHttpRequest';
    }

    /**
     * Determine if the request can accept a JSON response
     *
     * @return bool
     */
    public function wantsJson()
    {
        return strpos($this->header('ACCEPT', '*/*'), '/json') !== false;
    }

    /**
     * Determine if the request expects a JSON response
     *
     * @return bool
     */
    public function expectsJson()
    {
        return $this->ajax() || $this->wantsJson();
    }

    /**
     * Determine if the attribute exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return isset($this->request[$key]);
    }

    /**
     * Get an attribute or fallback
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Add an attribute to the request
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add(string $key, $value)
    {
        $this->request[$key] = $value;
    }

    /**
     * Remove the attribute from the request
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key)
    {
        unset($this->request[$key]);
    }

    /**
     * Get the request user
     *
     * @return mixed
     */
    public function user()
    {
        return call_user_func($this->getUserResolver());
    }

    /**
     * Set the user resolver
     *
     * @param \Closure $resolver
     * @return self
     */
    public function setUserResolver(Closure $resolver)
    {
        $this->userResolver = $resolver;

        return $this;
    }

    /**
     * Get the user resolver
     *
     * @return \Closure
     */
    public function getUserResolver()
    {
        return $this->userResolver ?: function () {
            //
        };
    }

    /**
     * Determine if an instance exists
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get an instance
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set an instance
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->add($key, $value);
    }

    /**
     * Unset any instances or bindings
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Return the object as an array
     *
     * @return array
     */
    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * Return the object as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->request;
    }

    /**
     * Return the object as a json string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->request);
    }

    /**
     * Return the request to be encoded as json
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->request;
    }

    /**
     * Return the request as a json string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Determine if the attribute exists
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return $this->has($key);
    }

    /**
     * Get an attribute or fallback
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Add an attribute to the request
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->add($key, $value);
    }
}
