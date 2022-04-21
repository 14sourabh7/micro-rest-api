<?php

namespace App\Db;

use Phalcon\Di\Injectable;

class OrderHelper extends Injectable
{



    /**
     * createID($id)
     * 
     * function to create  \MongoDB\BSON\ObjectID($id)
     *
     * @param [type] $id
     * @return void
     */
    private function createID($id)
    {
        return new \MongoDB\BSON\ObjectID($id);
    }



    /**
     * getAll()
     * 
     * function returning all the products
     *
     * @param [type] $key
     * @return json
     */
    public function getAll()
    {
        $result = $this->mongo->store->orders->find();
        return $this->setResponse($result);
    }

    public function getOrderByDate($start, $end)
    {
        $result =
            $this->mongo->store->orders->find(
                [
                    'date' => ['$gte' => $start, '$lte' => $end]
                ]
            );
        return $this->setResponse($result);
    }

    public function getOrderByDateFilter($start, $end, $filter)
    {
        $result =
            $this->mongo->store->orders->find(
                [
                    'date' =>
                    [
                        '$gte' => $start, '$lte' => $end
                    ], 'status' => $filter
                ]
            );
        return $this->setResponse($result);
    }



    public function postOrder($data)
    {
        $result =  $this->mongo->store->orders->insertOne($data);
        return $result;
    }

    public function putOrder($data)
    {

        $result =  $this->mongo->store->orders->updateOne(
            [

                '_id' => $this->createID($data['id'])
            ],
            [
                '$set' => $data
            ]
        );
        return $result;
    }



    /**
     * setResponse($result)
     * 
     * function preparing json response
     *
     * @param [type] $result
     * @return json
     */
    public function setResponse($result)
    {
        $response = [];

        foreach ($result as $k => $v) {
            $response[$k] = iterator_to_array($v);
            if (isset($response[$k]["_id"]))
                $response[$k]["_id"] = (array) $v->_id;
        }

        if (count($response) < 1) {
            $response = array("info" => "no data found for the given conditions");
        }
        return $response;
    }
}
