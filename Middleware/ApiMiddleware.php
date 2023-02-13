<?php

namespace Typhoeus\Api\Middleware;

use Closure;

class ApiMiddleware {

    /**
     * @var array $except
     */
    protected $except = [];

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null) {

        return $next($request);
    }
}
