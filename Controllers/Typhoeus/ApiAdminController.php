<?php
namespace Typhoeus\Api\Controllers\Typhoeus;

use Typhoeus\Api\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * [Description ApiProductsController]
 */
class ApiAdminController extends Controller
{

    /**
     * @var string[]
     */
    protected $roles = ['administrator', 'user', 'marketing', 'sales', 'partner'];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function process(Request $request): JsonResponse
    {
        //dd($request->body()->all());
        //$items = json_decode($request['products'],true);
        $requestType = $request['requestType'];
        $hashSessionData = $this->createSessionData($request);
        switch ($requestType) {
            case "disableUserHash":
            case "enableUserHash":
                return $this->userHash->$requestType($request,$hashSessionData);
            case "unlockUserHash":
                $userHash = $request['userHash'];
                $state = 0;
                $this->userHash->updateUserHashLock($userHash, $state);
                $request_id = $hashSessionData['request_id'];
                $response = app()->make('stdClass');
                $response->requestId = $request_id;
                return $this->sendResponse($response, "User hash {$userHash} unlocked successfully!");
            case "lockUserHash":
                $userHash = $request['userHash'];
                $state = 1;
                $this->userHash->updateUserHashLock($userHash, $state);
                $request_id = $hashSessionData['request_id'];
                $response = app()->make('stdClass');
                $response->requestId = $request_id;
                return $this->sendResponse($response, "User hash {$userHash} locked successfully!");
            case "assignUserHashRole":
                $userHash = $request['userHash'];
                $role = $request['role'];
                if(!in_array(strtolower($role), $this->roles)) {
                    return $this->sendError('Invalid role assignment');
                }
                $this->userHash->assignUserHashRole($userHash, $role);
                $request_id = $hashSessionData['request_id'];
                $response = app()->make('stdClass');
                $response->requestId = $request_id;
                return $this->sendResponse($response, "User hash {$userHash} assigned {$role} role successfully!");
            case "updateUserHashSecret":
                $userHash = $request['userHash'];
                $secret = $request['secret'] ?? false;
                if(!$secret) {
                    return $this->sendError('Invalid secret word value.');
                }
                $this->userHash->updateUserHashSecret($userHash, $secret);
                $request_id = $hashSessionData['request_id'];
                $response = app()->make('stdClass');
                $response->requestId = $request_id;
                return $this->sendResponse($response, "User hash {$userHash} updated secret word successfully!");
            case "updateUserHashPassword":
                $userHash = $request['userHash'];
                $password = $request['password'];
                $this->userHash->updateUserHashPassword($userHash, $password);
                $request_id = $hashSessionData['request_id'];
                $response = app()->make('stdClass');
                $response->requestId = $request_id;
                return $this->sendResponse($response, "User hash {$userHash} password updated successfully!");
            case "createUserHash":
                $email = $request['email'];
                $secret = $request['secret'];
                if(isset($email) && isset($secret)) {
                    if(!$this->userHash->createUserHash($email, $secret)) {
                        return $this->sendError('Invalid data to create User hash! Check user email and request format.');
                    } else {
                        $request_id = $hashSessionData['request_id'];
                        $response = app()->make('stdClass');
                        $response->requestId = $request_id;
                        return $this->sendResponse($response, "User hash with user {$email} created successfully!");
                    }
                }
                return $this->sendError('Incomplete data to create User hash!');
        }
        return $this->sendError('Incorrect request data!');
    }
}
