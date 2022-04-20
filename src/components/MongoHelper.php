<?php

namespace App\Db;

use Phalcon\Di\Injectable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MongoHelper extends Injectable
{

    /**
     * search($key, $keyword)
     * 
     * function to search 
     *
     * @param [type] $key
     * @param [type] $keyword
     * @return json
     */
    public function search($key, $keyword)
    {
        $keywords = [];
        $auth = $this->checkKey($key);
        if (!$this->checkKey($key)) {
            $response = $this->response->SetStatusCode(401);
            $response =  $this->response->setJsonContent(["status" => "error", "message" => "Invalid token"]);
            return $response;
            die;
        }
        foreach ($keyword as $key => $value) {
            array_push($keywords, array('name' => ['$regex' => $value]));
        }
        $result =  $this->mongo->store->products->find(['$or' => $keywords]);

        return $this->getJsonEncode($result);
    }


    /**
     * getAll($key)
     * 
     * function returning all the products
     *
     * @param [type] $key
     * @return json
     */
    public function getAll($key)
    {
        if (!$this->checkKey($key)) {
            return json_encode(array("error" => "Invalid token"));
            die;
        }
        $result = $this->mongo->store->products->find();
        return $this->getJsonEncode($result);
    }

    /**
     * get($key, $per_page, $page, $select, $filter)
     * 
     * function returning products based on conditions
     *
     * @param [type] $key
     * @param [type] $per_page
     * @param [type] $page
     * @param [type] $select
     * @param [type] $filter
     * @return json
     */
    public function get($key, $per_page, $page, $select, $filter)
    {
        if (!$this->checkKey($key)) {
            return json_encode(array("error" => "Invalid token"));
            die;
        }
        $columns = ["_id" => 0];

        foreach (explode(" ", urldecode($select)) as $column => $value) {
            $columns[$value] = 1;
        }

        $result = $this->mongo->store->products->find(['name' => ['$regex' => $filter]], [
            "skip" => (int)$page == 1 ? 0 : ((int) $page) - 1,
            "limit" => (int) $per_page,
            "projection" => $columns
        ]);

        return $this->getJsonEncode($result);
    }


    /**
     * checkKey($key)
     * 
     * function to check key
     *
     * @param [type] $key
     * @return bool
     */
    public function checkKey($key)
    {
        try {
            $publicKey = "g7CMIFzDk72oW2KvSQnRHR6/v7fX5CsrzM65MEIspCM=";
            $decoded = JWT::decode($key, new Key($publicKey, 'EdDSA'));
            $credentials = (array) $decoded;
            $user = $credentials['user'];
            $password = $credentials['password'];
            if ($user = 'sourabh' && $password == '12345') {
                return true;
            } else {
                die('hello');
            }
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * getJsonEncode($result)
     * 
     * function preparing json response
     *
     * @param [type] $result
     * @return json
     */
    public function getJsonEncode($result)
    {
        $response = [];

        foreach ($result as $k => $v) {
            $response[$k] = iterator_to_array($v);

            if (isset($response[$k]["_id"]))
                $response[$k]["_id"] = (array) $v->_id;

            if (isset($v->additional)) {

                $response[$k]['additional'] = iterator_to_array($v->additional);
            }
            if (isset($v->variation)) {
                $response[$k]['variation'] = iterator_to_array($v->variation);
                foreach ($v->variation as $key => $value) {
                    $response[$k]['variation'][$key] = iterator_to_array($value);
                }
            }
        }
        return $response;
    }
}
