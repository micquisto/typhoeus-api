<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Jenssegers\Mongodb\Eloquent\Model;
use Typhoeus\Api\Helpers\TyphoeusApiHelper as Helper;
use  Typhoeus\Catalog\Product;
use Typhoeus\Catalog\Checkout\ShippingMethod\Connectship;
class TyphoeusProducts extends Model
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
     * @var TyphoeusLocations
     */
    protected $vendors;

    /**
     * @var TyphoeusLocations
     */
    protected $vendor;

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
     * @var
     */
    protected $zip;

    public function __construct()
    {
        $this->vendor = new TyphoeusLocations();
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
        $model = $this->vendor;
        $this->vendors = $model->getLocations();
        return $this;
    }


    /**
     * @param $ids
     * @return array
     * @throws BindingResolutionException
     */
    public function getProducts($ids, $type = "sku", $zip = null)
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
