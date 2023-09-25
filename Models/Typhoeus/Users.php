<?php

namespace Typhoeus\Api\Models\Typhoeus;

use Typhoeus\Api\Models\SqlModel;

class Users extends SqlModel
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
     protected  $app;

     /**
     *
     */
    public function __construct()
    {
        $this->app = app();
        $this->connection = 'typhoeus';
        $this->table = 'users';
        parent::__construct();
    }

    /**
     * @param $email
     * @return $this
     */
    public function getUserByEmail($email): Users
    {
        $data = $this->select('id','email')->where('email',"=", $email)->first();
        $this->data = $data->attributes;
        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getUserById($id) {
        $data = $this->select('*')->where('id',"=", $id)->first();
        $this->data = $data->attributes;
        return $this;
    }

    /**
     * @param $id
     * @return $this|null
     */
    public function getUserAddressBilling($id)
    {
        if($id) {
            $data = $this->select('address_book.*')
                ->where('users.id',"=", $id)
                ->where('address_book.billing',"=", 1)
                ->leftJoin('user_address_book', 'user_address_book.user_id', '=', 'users.id')
                ->leftJoin('address_book', 'address_book.id', '=', 'user_address_book.address_book_id')
                ->orderByDesc('address_book.created_at')
                ->first();
            $this->data = $data->attributes;
            return $this;
        }
        return null;
    }
}
