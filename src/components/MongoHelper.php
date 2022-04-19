<?php

namespace App\Db;

use Exception;
use Phalcon\Di\Injectable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MongoHelper extends Injectable
{
    public function search($key, $keyword)
    {
        $keywords = [];
        if (!$this->checkKey($key)) {
            return json_encode(array("error" => "Invalid token"));
            die;
        }
        foreach ($keyword as $key => $value) {
            array_push($keywords, array('name' => ['$regex' => $value]));
        }
        $result =  $this->mongo->store->products->find(['$or' => $keywords]);
        $response = [];

        foreach ($result as $k => $v) {
            $response[$k] = iterator_to_array($v);

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
        return json_encode($response);
    }


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
            "skip" => (int)$page == 1 ? 1 : ((int) $page) - 1,
            "limit" => (int) $per_page,
            "projection" => $columns
        ]);


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
        return json_encode($response);
    }

    public function checkKey($key)
    {
        try {
            $publicKey = "g7CMIFzDk72oW2KvSQnRHR6/v7fX5CsrzM65MEIspCM=";
            $decoded = JWT::decode($key, new Key($publicKey, 'EdDSA'));
            $access = (array)$decoded;
            if ($access) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
