<?php

namespace Typhoeus\Api\Models\Typhoeus;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Typhoeus\Api\Models\SqlModel;
use Typhoeus\Api\Helpers\ApiHelper as Helper;

class Orders extends SqlModel
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
        $this->table = "api_orders";
        parent::__construct();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createOrder($data) {
        return $this->insertGetId($data);
    }
}
