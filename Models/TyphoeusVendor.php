<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class TyphoeusVendor extends Model
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
    protected $vendorsCodeNames = [];

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Application
     */
    private $app;

    /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->connection = 'typhoeus';
        $this->table = 'vendor_codename';
        parent::__construct();
    }


    /**
     * @return $this
     */
    public function loadVendorCodeNames()
    {
        $data = $this->get();
        foreach($data as $item) {
            $this->vendorsCodeNames[$item->vendor] = $item;
        }
        $this->data = $this->vendorsCodeNames;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
