<?php

namespace Typhoeus\Api\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class ApiMiddlewareGroup
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Application|Redirector|RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        /**
         * removed test
         */
        return redirect('home')->with('error','Permission Denied!!!');

    }
}
