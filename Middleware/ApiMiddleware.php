<?php

namespace Typhoeus\Api\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Typhoeus\Api\Models\TyphoeusUserHash;
use Typhoeus\Api\Helpers\TyphoeusApiHelper as Helper;

class ApiMiddleware
{
    /**
     * @var TyphoeusUserHash
     */
    protected $userHash;
    

    /**
     * 
     */
    public function __construct()
    {
        $this->userHash = new TyphoeusUserHash();
    }
    
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Application|Redirector|RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
	    //dd(json_encode($request->all()));
	//dd($request->getContent());
        $invalid = false;
        $message = "";
        $code = 404;
        $authKey = $request->header('Auth-Key');
        $authSignature = $request->header('Auth-Signature');
        $requestJson = json_encode($request->all());
        $hashSecret = null;
        if($authKey) {
            $hashSecret = $this->userHash->getHashSecret($authKey);
        } else {
            $invalid = true;
            $message = 'Permission denied!!';
        }
        if (!$invalid && ($hashSecret == null || $authSignature != md5($authKey.$hashSecret.$requestJson))) {
            $invalid = true;
            $message = 'Permission denied!!!';
            $code = 400;
        }
        $requestTypes = config(Helper::getPackageName() . '::apirequest.requests.types');
        if(!$request['requestType'] && ! $invalid) {
            $invalid = true;
            $message = 'Missing requestType.';
        } else if(!in_array($request['requestType'], $requestTypes) && !$invalid) {
            $invalid = true;
            $message = 'Invalid requestType.';
        } else if (!$request['shippingZip'] && !$invalid) {
            $invalid = true;
            $message = 'Missing shippingZip.';
            $code = 400;
        }  else if (!$request['products'] && !$invalid) {
            $invalid = true;
            $message = 'Missing products.';
            $code = 400;
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
