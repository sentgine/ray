<?php

namespace Sentgine\Ray;

use Sentgine\Ray\Http\Request;
use Sentgine\Ray\Http\Response;

class Route
{
    private static array $routes = [];
    private static ?string $notFoundTemplate = null;
    private static ?string $methodNotAllowedTemplate = null;
    private static string $currentGroupPrefix = '';

    /**
     * Registers a GET route.
     *
     * @param string $route The route pattern.
     * @param callable|string $handler The handler for the route.
     * @param array $middlewares The middlewares for the route.
     */
    public static function get(string $route, $handler, array $middlewares = []): void
    {
        self::$routes[] = ['GET', self::$currentGroupPrefix . $route, $handler, $middlewares];
    }

    /**
     * Registers a POST route.
     *
     * @param string $route The route pattern.
     * @param callable|string $handler The handler for the route.
     * @param array $middlewares The middlewares for the route.
     */
    public static function post(string $route, $handler, array $middlewares = []): void
    {
        self::$routes[] = ['POST', self::$currentGroupPrefix . $route, $handler, $middlewares];
    }

    /**
     * Registers a PUT route.
     *
     * @param string $route The route pattern.
     * @param callable|string $handler The handler for the route.
     * @param array $middlewares The middlewares for the route.
     */
    public static function put(string $route, $handler, array $middlewares = []): void
    {
        self::$routes[] = ['PUT', self::$currentGroupPrefix . $route, $handler, $middlewares];
    }

    /**
     * Registers a PATCH route.
     *
     * @param string $route The route pattern.
     * @param callable|string $handler The handler for the route.
     * @param array $middlewares The middlewares for the route.
     */
    public static function patch(string $route, $handler, array $middlewares = []): void
    {
        self::$routes[] = ['PATCH', self::$currentGroupPrefix . $route, $handler, $middlewares];
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $route The route pattern.
     * @param callable|string $handler The handler for the route.
     * @param array $middlewares The middlewares for the route.
     */
    public static function delete(string $route, $handler, array $middlewares = []): void
    {
        self::$routes[] = ['DELETE', self::$currentGroupPrefix . $route, $handler, $middlewares];
    }

    /**
     * Groups routes under a common prefix.
     *
     * @param string $prefix The prefix for the group.
     * @param callable $callback The callback to define the group routes.
     * @param array $middlewares The middlewares for the group.
     */
    public static function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousGroupPrefix = self::$currentGroupPrefix;
        self::$currentGroupPrefix .= $prefix;
        $callback(new self);
        self::$currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * Sets the template for the "Not Found" page.
     *
     * @param string $path
     */
    public static function setNotFoundTemplate(string $path): void
    {
        self::$notFoundTemplate = $path;
    }

    /**
     * Sets the template for the "Method Not Allowed" page.
     *
     * @param string $path
     */
    public static function setMethodNotAllowedTemplate(string $path): void
    {
        self::$methodNotAllowedTemplate = $path;
    }

    /**
     * Dispatches the request to the appropriate route handler.
     *
     * @param string $httpMethod The HTTP method of the request.
     * @param string $uri The URI of the request.
     */
    public static function dispatch(string $httpMethod, string $uri): void
    {
        $uri = rtrim($uri, '/');
        $routeInfo = self::findRoute($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case 'NOT_FOUND':
                echo view(self::$notFoundTemplate);
                break;
            case 'METHOD_NOT_ALLOWED':
                echo view(self::$methodNotAllowedTemplate);
                break;
            case 'FOUND':
                [$handler, $vars, $middlewares] = $routeInfo[1];
                self::handleRequest($handler, $vars, $middlewares);
                break;
        }
    }

    /**
     * Finds the route that matches the given HTTP method and URI.
     *
     * @param string $httpMethod The HTTP method of the request.
     * @param string $uri The URI of the request.
     * @return array The route information.
     */
    private static function findRoute(string $httpMethod, string $uri): array
    {
        $allowedMethods = [];

        foreach (self::$routes as $route) {
            [$method, $routeUri, $handler, $middlewares] = $route;
            $routeUri = rtrim($routeUri, '/');

            if (self::matchUri($routeUri, $uri, $vars)) {
                if ($httpMethod === $method) {
                    return ['FOUND', [$handler, $vars, $middlewares]];
                }

                $allowedMethods[] = $method;
            }
        }

        return empty($allowedMethods) ? ['NOT_FOUND'] : ['METHOD_NOT_ALLOWED'];
    }

    /**
     * Matches the URI with the route pattern.
     *
     * @param string $routeUri The route pattern.
     * @param string $uri The URI to match.
     * @param array $vars The variables extracted from the URI.
     * @return bool True if the URI matches the route pattern, false otherwise.
     */
    private static function matchUri(string $routeUri, string $uri, &$vars = []): bool
    {
        $routeParts = explode('/', $routeUri);
        $uriParts = explode('/', $uri);

        $vars = [];
        foreach ($routeParts as $index => $part) {
            if (preg_match('/^\{(\w+)\?\}$/', $part, $matches)) {
                if (isset($uriParts[$index])) {
                    $vars[$matches[1]] = $uriParts[$index];
                } else {
                    $vars[$matches[1]] = null;
                }
            } elseif (preg_match('/^\{(\w+)\}$/', $part, $matches)) {
                if (isset($uriParts[$index])) {
                    $vars[$matches[1]] = $uriParts[$index];
                } else {
                    return false;
                }
            } elseif ($part !== ($uriParts[$index] ?? null)) {
                return false;
            }
        }

        if (count($uriParts) > count($routeParts)) {
            return false;
        }

        return true;
    }

    /**
     * Handles the request by calling the appropriate handler.
     *
     * @param callable|string $handler The handler for the route.
     * @param array $vars The variables extracted from the URI.
     * @param array $middlewares The middlewares for the route.
     */
    private static function handleRequest($handler, array $vars, array $middlewares): void
    {
        $request = Request::createFromGlobals();

        $middlewareStack = array_reverse($middlewares);
        $next = function ($request) use ($handler, $vars) {
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
                        $params[] = self::instantiateClass($paramClass);
                    }
                }

                if (class_exists($class)) {
                    $controller = new $class();
                    if (method_exists($controller, $method)) {
                        return call_user_func_array([$controller, $method], $params);
                    }
                }
            } elseif (is_callable($handler)) {
                return call_user_func_array($handler, $vars);
            }

            return null;
        };

        foreach ($middlewareStack as $middleware) {
            $next = function ($request) use ($middleware, $next) {
                $middlewareInstance = new $middleware();
                return $middlewareInstance->handle($request, $next);
            };
        }

        $response = $next($request);
        if ($response instanceof Response) {
            $response->send();
        }
    }

    /**
     * Instantiates a class.
     *
     * @param string $className The name of the class to instantiate.
     * @return object The instantiated class.
     */
    private static function instantiateClass(string $className): object
    {
        $fullyQualifiedClassName = '\\' . $className;
        return new $fullyQualifiedClassName();
    }
}
