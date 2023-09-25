<?php
namespace Typhoeus\Api\Models\Typhoeus;

use Typhoeus\Api\Models\SqlModel;

class UserHash extends SqlModel
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
     * @var Users
     */
    protected $users;

    /**
     * @var array
     */
    protected $userHashes = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->users = new Users();
        $this->connection = "mysql-api";
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
     * @param $hash
     * @return null
     */
    public function getUserId($hash)
    {
        if($hash) {
            $data = $this->select('hash','user_id')->where('hash',"=", $hash)->first();
            if($data != null && !empty($data->user_id)) {
                return $data->user_id;
            }
        }
        return null;
    }

    /**
     * @param $key
     * @return array|mixed|UserHash
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
    public function updateUserHash($hash = null, $sessionId = null): UserHash
    {
        if ($hash && $sessionId) {
            $this->where('hash', '=', $hash)->update(array('session_id' => $sessionId));
        }
        return $this;
    }

    /**
     * @param $hash
     * @param $state
     * @return $this
     */
    public function updateUserHashLock($hash, $state): UserHash
    {
        if ($hash) {
            $this->where('hash', '=', $hash)->update(array('is_locked' => $state));
        }
        return $this;
    }

    /**
     * @param $hash
     * @param $role
     * @return $this
     */
    public function assignUserHashRole($hash, $role): UserHash
    {
        if ($hash) {
            $this->where('hash', '=', $hash)->update(array('role' => $role));
        }
        return $this;
    }

    /**
     * @param $hash
     * @param $password
     * @return $this
     */
    public function updateUserHashPassword($hash, $password): UserHash
    {
        if ($hash && $password) {
            $this->where('hash', '=', $hash)->update(array('password' => md5($password)));
        }
        return $this;
    }

    /**
     * @param $hash
     * @param $secret
     * @return $this
     */
    public function updateUserHashSecret($hash, $secret): UserHash
    {
        if ($hash && $secret) {
            $this->where('hash', '=', $hash)->update(array('password' => $secret));
        }
        return $this;
    }

    /**
     * @param $email
     * @param $secret
     * @return false
     */
    public function createUserHash($email, $secret): bool
    {
        if ($email && $secret) {
            /* TODO: Transfer this method to Users Helper */
            if($id = $this->users->getUserIdByEmail($email) == null) return false;
            /* end todo */

            $userHash = md5($email.date("Y-m-d H:i:s"));
            return $this->insert(
                [
                    'hash'    => $userHash,
                    'secret'    => $secret,
                    'created_at'  => date("Y-m-d H:i:s"),
                    'updated_at'    => date("Y-m-d H:i:s"),
                    'role'    => 'user',
                    'user_id' => $id,//19590,
                    'is_enabled' => 1,
                    'is_locked' => 0
                ]
            );
        }
        return false;
    }

    /**
     * @param $request
     * @param $hashSessionData
     * @return mixed
     */
    public function disableUserHash($request,$hashSessionData)
    {
        return $this->updateUserHashState($request, $hashSessionData, 0);
    }

    /**
     * @param $request
     * @param $hashSessionData
     * @return mixed
     */
    public function enableUserHash($request,$hashSessionData)
    {
        return $this->updateUserHashState($request, $hashSessionData, 1);
    }

    /**
     * @param $request
     * @param $hashSessionData
     * @param $state
     * @return mixed
     */
    public function updateUserHashState($request, $hashSessionData, $state)
    {
        $userHash = $request['userHash'] ?? false;
        $request_id = $hashSessionData['request_id']??false;
        if ($userHash) {
            $this->where('hash', '=', $userHash)->update(array('is_enabled' => $state));
        } else {
            return $this->sendError('Invalid user hash!');
        }
        if(!$request_id) {
            return $this->sendError('Invalid request id!');
        }
        $response = app()->make('stdClass');
        $response->requestId = $request_id;
        switch($state) {
            case 0: $action = 'disabled'; break;
            case 1:default: $action = 'enabled';
        }
        return $this->sendResponse($response, "User hash {$userHash} {$action} successfully!");
    }

    /**
     * @param $value
     * @return null
     */
    public function getUserHashData($value)
    {
        if($value) {
            $data = $this->select('*')->where('hash',"=", $value)->first();
            $this->data = $data->attributes;
            return $this;
        }
        return null;
    }
}
