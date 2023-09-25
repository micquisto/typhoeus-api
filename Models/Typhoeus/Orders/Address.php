<?php

namespace Typhoeus\Api\Models\Typhoeus\Orders;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Typhoeus\Api\Models\SqlModel;

class Address extends SqlModel
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
        $this->table = "api_order_address";
        parent::__construct();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createOrderAddress($data) {
        return $this->insertGetId($data);
    }
}
