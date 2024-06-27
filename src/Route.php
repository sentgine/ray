<?php

namespace Sentgine\Ray;

use Exception;

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
        $uri = rtrim($uri, '/');
        $routeInfo = $this->findRoute($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case 'NOT_FOUND':
                echo view($this->notFoundTemplate);
                break;
            case 'METHOD_NOT_ALLOWED':
                echo view($this->methodNotAllowedTemplate);
                break;
            case 'FOUND':
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->handleRequest($handler, $vars);
                break;
        }
    }

    /**
     * Finds a route that matches the given HTTP method and URI.
     *
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    private function findRoute(string $httpMethod, string $uri): array
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            [$method, $routeUri, $handler] = $route;
            $routeUri = rtrim($routeUri, '/');

            if ($this->matchUri($routeUri, $uri, $vars)) {
                if ($httpMethod === $method) {
                    return ['FOUND', $handler, $vars];
                }

                $allowedMethods[] = $method;
            }
        }

        return empty($allowedMethods) ? ['NOT_FOUND'] : ['METHOD_NOT_ALLOWED'];
    }

    /**
     * Matches the given URI against the route URI and extracts variables.
     *
     * @param string $routeUri
     * @param string $uri
     * @param array $vars
     * @return bool
     */
    private function matchUri(string $routeUri, string $uri, &$vars = []): bool
    {
        $routeParts = explode('/', $routeUri);
        $uriParts = explode('/', $uri);

        $vars = [];
        foreach ($routeParts as $index => $part) {
            if (preg_match('/^\{(\w+)\?\}$/', $part, $matches)) {
                // Optional parameter
                if (isset($uriParts[$index])) {
                    $vars[$matches[1]] = $uriParts[$index];
                } else {
                    $vars[$matches[1]] = null;
                }
            } elseif (preg_match('/^\{(\w+)\}$/', $part, $matches)) {
                // Required parameter
                if (isset($uriParts[$index])) {
                    $vars[$matches[1]] = $uriParts[$index];
                } else {
                    return false;
                }
            } elseif ($part !== ($uriParts[$index] ?? null)) {
                return false;
            }
        }

        // Ensure that all URI parts are matched
        if (count($uriParts) > count($routeParts)) {
            return false;
        }

        return true;
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
