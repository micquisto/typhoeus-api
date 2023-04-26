<?php
namespace Typhoeus\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Typhoeus\Api\Models\TyphoeusProducts;

/**
 * [Description ApiProductsController]
 */
class ApiProductsController extends Controller
{
    /**
     * @var TyphoeusUserSession
     */
    protected $userSession;
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BindingResolutionException
     */
    public function getProducts(Request $request)
    {
        //dd($request['products']);
        $ids = json_decode($request['products'],true);
        $dataType = $request['dataType'];
        $zip = $request['shippingZip'];
        $response = app()->make('stdClass');
        $products = new TyphoeusProducts();
        if(($p = $products->getProducts($ids, $dataType, $zip)) !== false) {
            $hashSessionData = $this->createSessionData($request);
            $sessionId = $hashSessionData['session_id'];
            $request_id = $hashSessionData['request_id'];
            $response->requestId = $request_id;
            $response->products = $p;
            return $this->sendResponse($response, 'Request processed successfully!');
        } else {
            return $this->sendError('Incorrect request data!');
        }

    }
}
