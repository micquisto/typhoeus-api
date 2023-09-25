<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Container\BindingResolutionException;
use Typhoeus\Api\Helpers\ApiHelper as Helper;

class SqlModel extends Model
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Application
     */
    protected  $app;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->helper = new Helper();
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
