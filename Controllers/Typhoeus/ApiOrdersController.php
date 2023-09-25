<?php
namespace Typhoeus\Api\Controllers\Typhoeus;

use Typhoeus\Api\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Typhoeus\Api\Helpers\Typhoeus\OrderBuilder;
use Typhoeus\Api\Helpers\Typhoeus\OrderProcessor;

/**
 * [Description ApiProductsController]
 */
class ApiOrdersController extends Controller
{


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function process(Request $request): JsonResponse
    {
        //dd($request->body()->all());
        $products = json_decode($request['products'],true);
        $this->products = $products['items'];
        $items = $this->products;
        $dataType = $products['type'];

        //order validation
        if($m = $this->orderValidator->notValidOrder($request)) {
            $x = "test";
            return $this->sendError($m);
        }

        //get user hash data
        $userHashData = $this->userHash->getUserHashData($request->header('Auth-Key'))->getData();
        //get user data
        $userData = $this->users->getUserById($userHashData['user_id'])->getData();
        $userBillingAddress = $this->users->getUserAddressBilling($userHashData['user_id'])->getData();
        //get order data
        $location = $this->locations->getLocationById($request['locationId'])->getData();
        //billing
        $billingAddress = $this->orderBuilder->buildBillingAddress($userBillingAddress, $location);
        if(!$billingAddress->getStatus()) {
            return $this->sendError($billingAddress->getMessage());
        }
        //shipping
        $shipTo = json_decode($request['shipTo'],true);
        $shippingAddress = $this->orderBuilder->buildShippingAddress($shipTo, $location);
        if(!$shippingAddress->getStatus()) {
            return $this->sendError($shippingAddress->getMessage());
        }
        //order items
        $orderItems = $this->buildOrderItems($items, $dataType, $location["vendorname"]);
        if(!$orderItems->getStatus()) {
            return $this->sendError($orderItems->getMessage());
        }
        $orderData = $this->orderBuilder->getOrderData();
        $orderData["user"] = $userData;
        if(($p = $this->processOrder($orderData, $dataType)) !== false) {
            if(!$this->orderProcessor->getStatus()) {
                return $this->sendError($p->getMessage());
            }
            $response = app()->make('stdClass');
            $hashSessionData = $this->createSessionData($request);
            $sessionId = $hashSessionData['session_id'];
            $request_id = $hashSessionData['request_id'];
            $response->requestId = $request_id;
            $response->order = $p;
            return $this->sendResponse($response, 'Request processed successfully!');
        } else {
            return $this->sendError('Incorrect request data!');
        }

    }

    /**
     * @param $items
     * @param $type
     * @param $vendor
     * @return OrderBuilder
     */
    private function buildOrderItems($items, $type, $vendor): OrderBuilder
    {
        return $this->orderBuilder->buildOrderItems($items, $type, $vendor);
    }

    /**
     * @param $orderData
     * @param $type
     * @return array|false
     */
    private function processOrder($orderData, $type)
    {
        if($this->orderBuilder->getDataType($type)) {
            return $this->orderProcessor->createOrder($orderData)->getOrderData();
        }
        return false;
    }

}
