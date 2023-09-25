<?php

namespace Typhoeus\Api\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Typhoeus\Api\Models\TyphoeusUserSession;
use Typhoeus\Api\Models\Typhoeus\UserHash;

use Typhoeus\Api\Models\Typhoeus\Orders;
use Typhoeus\Api\Models\TyphoeusProducts;
use Typhoeus\Api\Models\Typhoeus\Users;
use Typhoeus\Api\Models\Typhoeus\Locations;
use Typhoeus\Api\Helpers\Typhoeus\OrderBuilder;

use Typhoeus\Api\Helpers\Typhoeus\OrderValidator;
use Typhoeus\Api\Helpers\Typhoeus\OrderProcessor;



class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var TyphoeusUserSession
     */
    protected $userSession;

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var TyphoeusProducts
     */
    protected $products;

    /**
     * @var OrderValidator
     */
    protected $orderValidator;

    /**
     * @var TyphoeusUsers
     */
    protected $users;

    /**
     * @var UserHash
     */
    protected $userHash;

    /**
     * @var Locations
     */
    protected $locations;

    /**
     * @var OrderBuilder
     */
    protected $orderBuilder;

    /**
     * @var OrderProcessor
     */
    protected $orderProcessor;

    /**
     * @param Orders $orders
     * @param TyphoeusProducts $products
     * @param TyphoeusUserSession $userSession
     * @param OrderValidator $orderValidator
     * @param UserHash $userHash
     * @param Users $users
     * @param Locations $locations
     * @param OrderBuilder $orderBuilder
     * @param OrderProcessor $orderProcessor
     */
    public function __construct(
        Orders              $orders,
        TyphoeusProducts    $products,
        TyphoeusUserSession $userSession,
        OrderValidator      $orderValidator,
        UserHash    $userHash,
        Users       $users,
        Locations $locations,
        OrderBuilder $orderBuilder,
        OrderProcessor $orderProcessor
    )
    {
        $this->userHash = $userHash;
        $this->orders = $orders;
        $this->products = $products;
        $this->userSession = $userSession;
        $this->orderValidator = $orderValidator;
        $this->locations = $locations;
        $this->users = $users;
        $this->orderBuilder = $orderBuilder;
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * @param $result
     * @param $message
     * @return JsonResponse
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
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($error, array $errorMessages = [], int $code = 404)
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
        $hashSession = $this->userSession->generateRequestId($request);
        $userHash = $this->userHash;
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
