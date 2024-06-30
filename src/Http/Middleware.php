<?php

namespace Sentgine\Ray\Http;

use Closure;
use Sentgine\Ray\Http\Request;
use Sentgine\Ray\Http\Response;
use Sentgine\Ray\Interfaces\MiddlewareInterface;

/**
 * Abstract class Middleware
 *
 * Base class for middleware implementations.
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next The next middleware or route handler.
     * @return Response|null The HTTP response, or null if the request should be passed to the next middleware.
     */
    public function handle(Request $request, Closure $next): ?Response
    {
        // Call the next middleware or route handler and explicitly cast the return value
        return $next($request); // No need for explicit casting
    }
}
