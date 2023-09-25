<?php
/**
 * API Order Builder Helper
 */
namespace Typhoeus\Api\Helpers\Typhoeus;

use Typhoeus\Api\Helpers\ApiHelper;
use Typhoeus\Api\Models\Typhoeus\Orders;
use Typhoeus\Api\Models\TyphoeusProducts;
class OrderBuilder extends ApiHelper{

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var bool
     */
    protected $status = true;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var null[]
     */
    protected $addresses = ["billing"=>null,"shipping"=>null];

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var float
     */
    protected $totalWeight = 0.00;

    /**
     * @var float
     */
    protected $totalPrice = 0.00;

    /**
     * @var string[]
     */
    private $addressRequiredFields = [
        "first_name",
        "last_name",
        "company",
        "address1",
        "address2",
        "city",
        "state",
        "zipcode",
        "country",
        "phone",
        "vendor"
    ];

    /**
     * @var Locations
     */
    protected $locations;

    /**
     * @var TyphoeusProducts
     */
    protected $products;

    /**
     * @param Orders $orders
     */
    public function __construct(
        Orders $orders,
        TyphoeusProducts $products
    ) {
        $this->orders = $orders;
        $this->products = $products;
        parent::__construct();
    }

    /**
     * @param $userBillingAddress
     * @param $location
     * @return $this|OrderBuilder
     */
    public function buildBillingAddress($userBillingAddress, $location): OrderBuilder
    {
        return $this->buildAddress(
            [
                "first_name" => $userBillingAddress["first_name"],
                "last_name" => $userBillingAddress["last_name"],
                "company" => $userBillingAddress["company"],
                "address1" => $userBillingAddress["address_line_1"],
                "address2" => $userBillingAddress["address_line_2"],
                "city" => $userBillingAddress["city"],
                "state" => $userBillingAddress["state"],
                "zipcode" => $userBillingAddress["zip_code"],
                "country" => $userBillingAddress["country"],
                "phone" => $userBillingAddress["phone_number"],
                "vendor" => $location["vendorname"],
            ],
            "billing"
        );
    }

    /**
     * @param $shipTo
     * @param $location
     * @return $this|OrderBuilder
     */
    public function buildShippingAddress($shipTo, $location): OrderBuilder
    {
        return $this->buildAddress(
            [
                "first_name" => $shipTo["firstname"],
                "last_name" => $shipTo["lastname"],
                "company" => $shipTo["company"],
                "address1" => $shipTo["address1"],
                "address2" => $shipTo["address2"],
                "city" => $shipTo["city"],
                "state" => $shipTo["region"],
                "zipcode" => $shipTo["zipcode"],
                "country" => $shipTo["country"],
                "phone" => $shipTo["telephone"],
                "vendor" => $location["vendorname"],
            ],
            "shipping"
        );
    }

    /**
     * @param $data
     * @param $type
     * @return $this
     */
    public function buildAddress($data, $type): OrderBuilder
    {
        foreach($data as $key=>$datum) {
            if(!in_array($key, $this->addressRequiredFields)) {
                $this->status = false;
                $this->message = "Invalid order address format.";
                break;
            }
        }
        if($this->status) {
            $this->addAddressData($data, $type);
        }
        return $this;
    }

    /**
     * @param $items
     * @param $type
     * @param $vendor
     * @return $this
     */
    public function buildOrderItems($items, $type, $vendor): OrderBuilder
    {
        $ids = array_keys($items);
        $orderItems = $this->products->getProductsByIdRaw($ids, $type, $vendor);
        foreach ($orderItems as $item) {
            if($item->priceLine == 'DISCONTI') {
                $this->status = false;
                $this->message = "Product {$item->productId} is discontinued.";
                break;
            }
            if($item->availableQty < 1) {
                $this->status = false;
                $this->message = "Product {$item->productId} is unavailable.";
                break;
            }
            if($item->availableQty < $items[$item->productId]) {
                $this->status = false;
                $this->message = "Product {$item->productId} exceeded the available quantity.";
                break;
            }
            if($this->status) {

                $data = [
                    "product_id" => $item->productId,
                    "title" => $item->title,
                    "price" => $item->price,
                    "qty" => $items[$item->productId],
                    "vendor" => $item->vendor,
                    "leadtime" => $item->leadtime,
                    "weight" => $item->weight
                ];
                $this->addItemsData($data);
                $this->totalWeight += $item->weight;
                $this->totalPrice += $item->price;
            }
        }

        return $this;
    }

    /**
     * @param $data
     * @return void
     */
    protected function addItemsData($data) {
        $this->items[] = $data;
    }

    /**
     * @param $data
     * @param $type
     * @return void
     */
    protected function addAddressData($data, $type) {
        $this->addresses["{$type}"] = $data;
    }

    /**
     * @return bool
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return array[]|\null[][]
     */
    public function getOrderData() {
        return $this->status ?
            [
                "items" => $this->items,
                "orderTotalWeight" => $this->totalWeight,
                "grandTotalRaw" => $this->totalPrice,
                "addresses" => $this->addresses
            ]
            : null;
    }
}