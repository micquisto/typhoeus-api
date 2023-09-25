<?php
namespace Typhoeus\Api\Models\Typhoeus;

use Typhoeus\Api\Models\SqlModel;

class Locations extends SqlModel
{
    /**
     * @var array
     */
    protected $vendorsCodeNames = [];

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
        $this->table = "location_ids";
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
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if($key != null) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : [];
        }
        return $this->data;
    }

    /**
     * @param $id
     * @return array|bool|mixed
     */
    public function getLocationById($id)
    {
        if($id) {
            $data = $this->select('*')->where('id',"=", $id)->first();
            if($data !== null) {
                $this->data = $data->attributes;
                return $this;
            }
        }
        return null;
    }

}
