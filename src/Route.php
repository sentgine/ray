<?php

namespace Sentgine\Ray;

use Exception;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Route
{
    private array $routes = [];
    private ?string $notFoundTemplate = null;
    private ?string $methodNotAllowedTemplate = null;
    private string $currentGroupPrefix = '';

    /**
     * Registers a GET route.
     *
     * @param string $route
     * @param mixed $handler
     */
    public function get(string $route, $handler): void
    {
        $this->routes[] = ['GET', $this->currentGroupPrefix . $route, $handler];
    }

    /**
     * Registers a POST route.
     *
     * @param string $route
     * @param mixed $handler
     */
    public function post(string $route, $handler): void
    {
        $this->routes[] = ['POST', $this->currentGroupPrefix . $route, $handler];
    }

    /**
     * Registers a PUT route.
     *
     * @param string $route
     * @param mixed $handler
     */
    public function put(string $route, $handler): void
    {
        $this->routes[] = ['PUT', $this->currentGroupPrefix . $route, $handler];
    }

    /**
     * Registers a PATCH route.
     *
     * @param string $route
     * @param mixed $handler
     */
    public function patch(string $route, $handler): void
    {
        $this->routes[] = ['PATCH', $this->currentGroupPrefix . $route, $handler];
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $route
     * @param mixed $handler
     */
    public function delete(string $route, $handler): void
    {
        $this->routes[] = ['DELETE', $this->currentGroupPrefix . $route, $handler];
    }

    /**
     * Groups routes under a common prefix.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix .= $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * Sets the template for the "Not Found" page.
     *
     * @param string $path
     */
    public function setNotFoundTemplate(string $path): void
    {
        $this->notFoundTemplate = $path;
    }

    /**
     * Sets the template for the "Method Not Allowed" page.
     *
     * @param string $path
     */
    public function setMethodNotAllowedTemplate(string $path): void
    {
        $this->methodNotAllowedTemplate = $path;
    }

    /**
     * Dispatches the request to the appropriate route handler.
     *
     * @param string $httpMethod
     * @param string $uri
     * @throws Exception
     */
    public function dispatch(string $httpMethod, string $uri): void
    {
        try {
            $dispatcher = simpleDispatcher(function (RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route[0], $route[1], $route[2]);
                }
            });

            $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    echo view($this->notFoundTemplate);
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    echo view($this->methodNotAllowedTemplate);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $this->handleRequest($handler, $vars);
                    break;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Handles the request by invoking the appropriate handler.
     *
     * @param mixed $handler
     * @param array $vars
     */
    private function handleRequest($handler, array $vars): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;

            $reflectionMethod = new \ReflectionMethod($class, $method);
            $parameters = $reflectionMethod->getParameters();

            $params = [];

            foreach ($parameters as $parameter) {
                $paramName = $parameter->getName();
                $paramType = $parameter->getType();

                if (array_key_exists($paramName, $vars)) {
                    $params[] = $vars[$paramName];
                } elseif ($paramType) {
                    $paramClass = $paramType->getName();
                    $params[] = $this->instantiateClass($paramClass);
                }
            }

            if (class_exists($class)) {
                $controller = new $class();
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $params);
                }
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $vars);
        }
    }

    /**
     * Dynamically instantiates a class based on its fully qualified class name.
     *
     * @param string $className The fully qualified class name.
     * @return object An instance of the class.
     */
    private function instantiateClass(string $className): object
    {
        $fullyQualifiedClassName = '\\' . $className;
        return new $fullyQualifiedClassName();
    }
}
