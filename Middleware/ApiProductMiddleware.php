<?php

namespace Typhoeus\Api\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Typhoeus\Api\Helpers\ApiHelper as Helper;

class ApiProductMiddleware extends ApiMiddleware
{

    /**
     * * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Application|RedirectResponse|Redirector|mixed
     */
    public function handle(Request $request, Closure $next) {
        //return response("Blocked by API Product Middleware", 400);

        $invalid = false;
        $message = "";
        $code = 404;
        $requestTypes = config(Helper::getPackageName() . '::apirequest.requests.types.product');
        if (!$request['shippingZip']) {
            $invalid = true;
            $message = 'Missing shippingZip.';
            $code = 400;
        }  else if (!$request['products']) {
            $invalid = true;
            $message = 'Missing products.';
            $code = 400;
        } else if(!in_array($request['requestType'], $requestTypes) && !$invalid) {
            $invalid = true;
            $message = 'Invalid requestType.';
        }
        if($invalid) {
            $response = [
                'success'   => false,
                'message'   => $message
            ];
            return response($response, $code);
        }

        return $next($request);
    }
}
