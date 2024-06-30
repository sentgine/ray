<?php

namespace Sentgine\Ray\Interfaces;

use Closure;
use Sentgine\Ray\Http\Request;
use Sentgine\Ray\Http\Response;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next The next middleware or route handler.
     * @return Response|null The HTTP response, or null if the request should be passed to the next middleware.
     */
    public function handle(Request $request, Closure $next): ?Response;
}
