<?php

namespace Typhoeus\Api\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

class TyphoeusUserSession extends Model
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
     private  $app;

     /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->connection = 'typhoeus';
        $this->table = 'api_user_session';
        parent::__construct();
    }

    /**
     * @param $hash
     * @return int
     */
    protected function getLastRequestId()
    {
        $data = $this->select('request_id')->orderBy('request_id', 'DESC')->first();
        if($data != null) {
            return $data->request_id;
        }
        return 99999999;
    }

    /**
     * @param $request
     * @return array
     */
    public function generateRequestId($request) {
        $requestId = (int)$this->getLastRequestId() + 1;
        $sessionId = md5($requestId.date("Y-m-d H:i:s"));
        $this->insert(
            [
                'request_id'    => $requestId,
                'session_id'    => $sessionId,
                'request_body'  => json_encode($request),
                'ip_address'    => $request->ip(),
                'created_at'    => now()
            ]
        );
        return [
            'request_id'    => $requestId,
            'session_id'    => $sessionId
        ];
    }

}
