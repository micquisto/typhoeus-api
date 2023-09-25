<?php

namespace Typhoeus\Api\Models\Typhoeus\Orders;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Typhoeus\Api\Models\SqlModel;
use Typhoeus\Api\Helpers\ApiHelper as Helper;

class Shipment extends SqlModel
{

    /**
     * @var
     */
    protected $data;

    /**
     *
     */
    public function __construct()
    {
        $this->connection = "mysql-api";
        $this->table = "api_order_shipment";
        parent::__construct();
    }

}
