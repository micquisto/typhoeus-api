<?php

namespace Typhoeus\Api\Models\Typhoeus\Users;

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
        $this->connection = "typhoeus";
        $this->table = "user_address_book";
        parent::__construct();
    }

}
