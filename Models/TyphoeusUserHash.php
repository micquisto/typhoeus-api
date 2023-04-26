<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class TyphoeusUserHash extends Model
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
    protected $userHashes = [];

    /**
     * @var array
     */
    protected $data = [];

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
        $this->table = 'api_user_hash';
        parent::__construct();
    }

    /**
     * @param $hash
     * @return null
     */
     public function getHashSecret($hash)
    {
        if($hash) {
            $data = $this->select('hash','secret')->where('hash',"=", $hash)->first();
            if($data != null && !empty($data->secret)) {
                return $data->secret;
            }
        }
        return null;
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

    /**
     * @param $hash
     * @param $sessionId
     * @return $this
     */
    public function updateUserHash($hash = null, $sessionId = null) {
        if ($hash && $sessionId) {
            $this->where('hash', '=', $hash)->update(array('session_id' => $sessionId));
        }
        return $this;
    }

}
