<?php

namespace Typhoeus\Api\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Typhoeus\Api\Models\Typhoeus\UserHash;

class ApiMiddleware
{
    /**
     * @var UserHash
     */
    protected $userHash;


    /**
     * @param UserHash $userHash
     */
    public function __construct(
        UserHash $userHash
    )
    {
        $this->userHash = $userHash;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Application|Redirector|RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
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
        if(!$request['requestType'] && !$invalid) {
            $invalid = true;
            $message = 'Missing requestType.';
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
