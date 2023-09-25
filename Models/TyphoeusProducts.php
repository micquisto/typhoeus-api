<?php

namespace Typhoeus\Api\Models;

use Typhoeus\Api\Models\MongodbModel;
use Typhoeus\Api\Helpers\ApiHelper as Helper;
use  Typhoeus\Catalog\Product;
use Typhoeus\Catalog\Checkout\ShippingMethod\Connectship;
use Typhoeus\Api\Models\Typhoeus\Locations;
class TyphoeusProducts extends MongodbModel
{
    /**
     * @var string
     */
    protected $collection;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var mixed
     */
    protected $productData;

    /**
     * @var Locations
     */
    protected $vendors;

    /**
     * @var Locations
     */
    protected $vendorLocations;

    /**
     * @var Helper 
     */
    protected $helper;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Connectship
     */
    protected $ship;

    /**
     * @var string
     */
    protected $orderLocation = "";

    /**
     * @var
     */
    protected $zip;

    /**
     *
     */
    public function __construct()
    {
        $this->vendorLocations = new Locations();
        $this->helper = new Helper();
        $this->product = new Product();
        $this->ship = new Connectship();
        $this->app = app();
        $this->connection = 'mongodb-jenssegers';
        $this->collection = 'products';
        parent::__construct();
        $this->generateVendors();
    }

    /**
     * @param $query
     * @return $this
     */
    public function scopeLatest($query): TyphoeusProducts
    {
        $query->orderByAsc('productId');
        return $this;
    }

    /**
     * @return $this
     */
    protected function generateVendors(): TyphoeusProducts
    {
        $model = $this->vendorLocations;
        $this->vendors = $model->getLocations();
        return $this;
    }


    /**
     * @param $ids
     * @param string $type
     * @param $zip
     * @return array|false
     */
    public function getProducts($ids, string $type = "sku", $zip = null)
    {
        $this->zip = $zip;
        if($type = $this->helper->getDataType($type)) {
            if(!is_array($ids)) $ids = [$ids];
            //$data = $this->select('productId','inventory')->latest()->paginate(2);
            $data =$this->select('productId','inventory', 'dimensions')->whereIn($type, $ids)->orderBy($type, 'DESC')->get();
            //print_r($data);die();
            $data = $data->map(function ($product) {
                return $this->buildProductData($product);
            });
            $output = [];
            foreach($data as $item) {
                $output[$item->productId] = $item;
            }
            return $output;
        }
        return false;

    }

    /**
     * @param $ids
     * @param string $type
     * @return array|false
     */
    public function getProductsById($ids, string $type = "sku")
    {
        if($type = $this->helper->getDataType($type)) {
            if(!is_array($ids)) $ids = [$ids];
            //$data = $this->select('productId','inventory')->latest()->paginate(2);
            $data = $this->select('productId','inventory', 'dimensions')
                ->whereIn($type, $ids)->orderBy($type, 'DESC')->get();
            $data = $data->map(function ($product) {
                return $this->buildProductData($product);
            });
            $output = [];
            foreach($data as $item) {
                $output[$item->productId] = $item;
            }
            return $output;
        }
        return false;

    }

    /**
     * @param $ids
     * @param $type
     * @param $vendor
     * @return $this
     */
    public function getProductsByIdRaw($ids, $type, $vendor)
    {
        if($type = $this->helper->getDataType($type)) {
            if(!is_array($ids)) $ids = [$ids];
            $this->orderLocation = $vendor;
            $data = $this->select('*')
                ->whereIn($type, $ids)->orderBy($type, 'DESC')->get();
            $data = $data->map(function ($attribute) {
                return $this->buildRawItems($attribute);
            });
            if($data !== null) {
                return $data;
            }
        }
        return $this;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    protected function buildRawItems($attribute)
    {
        $inventory = $attribute->inventory;
        $vendor = $attribute->vendor;
        $item = $this->app->make('stdClass');
        $item->productId = $attribute->productId;
        $item->title = $attribute->title;
        $item->priceLine = $attribute->priceLine;
        $item->price = $attribute->pricing['price'];
        //echo $attribute->pricing['price'];
        $item->vendor = $vendor;
        $item->leadtime = $attribute->leadtime;
        $item->weight = $attribute->dimensions['weight'] ?? 0;
        if(isset($inventory['availability'])){
            $availability = $inventory['availability'];
            if(isset($availability[$this->orderLocation])) {
                $item->availableQty = $availability[$this->orderLocation]['qty'];
                $item->price = $availability[$this->orderLocation]['price']==0?
                    $item->price:$availability[$this->orderLocation]['price'];
            } else {
                $item->availableQty = 0;
            }
        }
        return $item;
    }


    /**
     * @param $product
     * @param string $requestType
     * @return mixed
     * @throws BindingResolutionException
     */

    protected function buildProductData($product, string $requestType = 'getPriceAndAvail')
    {
        $this->productData = $this->app->make('stdClass');
        $this->productData->productId = $product->productId;
        $this->$requestType($product);
        return $this->productData;
    }


    /**
     * @param $product
     * @return TyphoeusProducts
     * @throws BindingResolutionException
     */
    protected function getPriceAndAvail($product): TyphoeusProducts
    {
        $pArray = $product->toArray();
        $priceAndAvail = [];
        if(isset($pArray['inventory'])) {
            $inventory =$pArray['inventory'];
            $dimensions = $pArray['dimensions'];
            $shipping = $this->helper->estimateShipping($this->zip, $dimensions['weight']);
            if(isset($inventory['availability'])) {
                foreach($inventory['availability'] as $k=>$availability) {
                    foreach ($this->getVendor($k) as $vk => $vendor) {
                        if(isset($availability['qty']) && $availability['qty'] > 0) {
                            $availabilityData = $this->app->make('stdClass');
                            $availabilityData->location = $vendor['codename']. " ".$dimensions['weight'];
                            $availabilityData->locationId = $vendor['id'];
                            $availabilityData->availability = $availability['qty'] ?? 0;
                            $availabilityData->price = $availability['price'];//$this->product->getPrice($k, true);//$availability['price'];
                            $availabilityData->shipping = (float)$shipping ;
                            //$availability['cost'];
                            $priceAndAvail[] = $availabilityData;
                        }
                    }

                }
            }
        }
        $this->productData->priceAndAvail = $priceAndAvail;
        return $this;
    }

    /**
     * @param $vendor
     * @return mixed
     */
    protected function getVendor($vendor)
    {
        return $this->vendors->getData($vendor);
    }
}
