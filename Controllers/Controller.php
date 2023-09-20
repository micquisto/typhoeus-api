<?php

namespace Typhoeus\Api\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Typhoeus\Api\Models\TyphoeusUserSession;
use Typhoeus\Api\Models\TyphoeusUserHash;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var TyphoeusUserSession
     */
    protected $userSession;

    /**
     * @param $result
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message) 
    {
        $response = [
            'success'   => true,
            'data'      => $result,
            'message'   => $message
        ];
        return response()->json($response,200);
    }

    /**
     * @param $error
     * @param $errorMessages
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success'   => false,
            'message'   => $error
        ];
        
        if(!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response,$code);
    }

    /**
     * @param $request
     * @return array
     */
    public function createSessionData($request) {
        $this->userSession = new TyphoeusUserSession();
        $hashSession = $this->userSession->generateRequestId($request);
        $userHash = new TyphoeusUserHash();
        $sessionId = $hashSession['session_id'];
        $requestId = $hashSession['request_id'];
        $hash = $request->header('Auth-Key');
        $userHash->updateUserHash($hash, $sessionId);
        return [
            'request_id'    => $requestId,
            'session_id'    => $sessionId
        ];
    }
}
