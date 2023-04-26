<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class TyphoeusLocations extends Model
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
     * @var
     */
    protected $data;

    /**
     * @var Application
     */
     private  $app;

     /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->connection = 'typhoeus';
        $this->table = 'location_ids';
        parent::__construct();
    }

    /**
     * @return $this
     */
    public function getLocations()
    {
        $data = $this->get();
        foreach($data as $item) {
            $this->vendorsCodeNames[$item->vendorname][] = $item;
        }
        $this->data = $this->vendorsCodeNames;
        return $this;
    }

    /**
     * @param $key
     * @return $this->data
     */
    public function getData($key = null)
    {
        if($key != null) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : [];
        }
        return $this->data;
    }

}
