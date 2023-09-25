<?php
/**
 * API Order Processor Helper
 */
namespace Typhoeus\Api\Helpers\Typhoeus;

use Typhoeus\Api\Helpers\ApiHelper;
use Typhoeus\Api\Models\Typhoeus\Orders;
use Typhoeus\Api\Models\Typhoeus\Orders\Items;
use Typhoeus\Api\Models\Typhoeus\Orders\Address;
use Typhoeus\Api\Models\Typhoeus\Locations;
class OrderProcessor extends ApiHelper{

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var Items
     */
    protected $items;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var mixed|string
     */
    protected $orderIdPrefix;

    /**
     * @var Locations
     */
    protected $locations;

    /**
     * @var bool
     */
    protected $status = true;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var array
     */
    protected $orderData = [];

    /**
     * @param Orders $orders
     * @param Locations $locations
     */
    public function __construct(
        Orders $orders,
        Locations $locations,
        Items $items,
        Address $address,
        $orderIdPrefix = 'API'
    ) {
        $this->locations = $locations;
        $this->orders = $orders;
        $this->items = $items;
        $this->address = $address;
        $this->orderIdPrefix = $orderIdPrefix;
        parent::__construct();
    }

    /**
     * @param $orderData
     * @return $this
     */
    public function createOrder($orderData): OrderProcessor
    {
        if(!$orderId = $this->generateOrder($orderData)) {
            $this->status = false;
            $this->message = "Placing order failed. Try again.";
        } else {
            return $this->populateOrderItems($orderId, $orderData['items'])
                ->populateOrderAddress($orderId, $orderData['addresses']);
        }
        return $this;
    }

    /**
     * @param $orderData
     * @return mixed
     */
    private function generateOrder($orderData)
    {
        $userId = $orderData['user']['id'];
        $userEmail = $orderData['user']['email'];
        $totalWeight = $orderData['orderTotalWeight'];
        $zipcode = $orderData['addresses']['shipping']['zipcode'];
        $shipping = $this->estimateShipping($zipcode, $totalWeight);
        $totalPrice = $orderData['grandTotalRaw'];
        $orderId =  $orderData["orderId"] = $this->orderIdPrefix.hexdec(hash('adler32', $userId.date('Y-m-d H:i:s')));
        $taxes = 0.00;
        $discount = 0.00;
        $this->orderData = $orderData;
        return $this->orders->createOrder([
            'order_id'          => $orderId,
            'user_id'           => $userId,
            'email'             => $userEmail,
            'subtotal'          => $totalPrice,
            'shipping'          => $shipping,
            'taxes'             => 0.00, //to caclculate
            'discount'          => 0.00,
            'grand_total'       => ($totalPrice+$taxes)-$discount,
            'payment_method'    => 'no_payment_method',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @param $orderId
     * @param $items
     * @return $this
     */
    private function populateOrderItems($orderId, $items): OrderProcessor
    {
        if(!empty($items) && $this->status) {
            foreach($items as $key => $item) {
                if(!$this->items->createOrderItems([
                    "parent_id" => $orderId,
                    "vendor"    => !is_null($item['vendor'])?$item['vendor']:"",
                    "qty"    => $item['qty'],
                    "price"    => $item['price'],
                    "leadtime"    => $item['leadtime'],
                    "title"    => $item['title'],
                    "product_id"    => $item['product_id'],
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ]))
                {
                    $this->status = false;
                    $this->message = "Cannot populate order items. Try again.";
                    break;
                }
            }

        }
        return $this;
    }

    /**
     * @param $orderId
     * @param $addresses
     * @return $this
     */
    private function populateOrderAddress($orderId, $addresses): OrderProcessor
    {
        if(!empty($addresses) && $this->status) {
            foreach($addresses as $key => $address) {
                if(!$this->address->createOrderAddress([
                    "parent_id"     => $orderId,
                    "type"          => $key,
                    "first_name"    => $address['first_name'],
                    "last_name"    => $address['last_name'],
                    "company"    => $address['company'],
                    "address1"    => $address['address1'],
                    "address2"    => $address['address2'],
                    "city"    => $address['city'],
                    "state"    => $address['state'],
                    "zipcode"    => $address['zipcode'],
                    "country"    => $address['country'],
                    "phone"    => $address['phone'],
                    "vendor"    => $address['vendor'],
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                ]))
                {
                    $this->status = false;
                    $this->message = "Cannot populate order addresses. Try again.";
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getOrderData() {
        return $this->status?$this->orderData:[];
    }

}