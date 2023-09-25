<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Jenssegers\Mongodb\Eloquent\Model;
use Typhoeus\Api\Helpers\ApiHelper as Helper;
class MongodbModel extends Model
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
     * @var Helper 
     */
    protected $helper;

    /**
     * @var array
     */
    protected $data = [];

    /**
     *
     */
    public function __construct()
    {
        $this->helper = new Helper();
        $this->app = app();
        parent::__construct();
    }

    /**
     * @param $key
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if($key != null) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : [];
        }
        return $this->data;
    }

}
