<?php
/**
 * API Helper
 */
namespace Typhoeus\Api\Helpers\Typhoeus;

use Typhoeus\Api\Helpers\ApiHelper;
use Typhoeus\Api\Models\Typhoeus\Orders;
use Typhoeus\Api\Models\Typhoeus\Locations;
class OrderValidator extends ApiHelper{

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var Locations
     */
    protected $locations;

    /**
     * @param Orders $orders
     * @param Locations $locations
     */
    public function __construct(
        Orders $orders,
        Locations $locations
    ) {
        $this->locations = $locations;
        $this->orders = $orders;
        parent::__construct();
    }

    /**
     * @param $request
     * @return false|string
     */
    public function notValidOrder($request) {
        //validate request entry and data type
        //locationId
        if(isset($request['locationId'])
            && $this->locations->getLocationById($request['locationId'])->getData() === null)
            return "Location id not found.";
        if(!is_numeric($request['locationId'])) return "Location id invalid data type.";

        //products
        if(!$this->isJson($request['products'])) return "Product invalid data type.";

        //shipTo
        if(!$this->isJson($request['shipTo'])) return "ShipTo invalid data type.";

        //return "Invalid order";
        return false;
    }

}