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


    /**
     * functon to get order filtered by date from db
     *
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
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



    /**
     * function to get orders filtered by date and status from db
     *
     * @param [type] $start
     * @param [type] $end
     * @param [type] $filter
     * @return void
     */
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



    /**
     * function to create new order
     *
     * @param [type] $data
     * @return void
     */
    public function postOrder($data)
    {
        $product = new \App\Db\ProductHelper();
        $result = $product->getSingle($data['product_id']);

        if (count($result) > 0) {
            if ($result[0]['stock'] >= $data['quantity']) {
                $result[0]['stock'] = $result[0]['stock'] - $data['quantity'];
                $product->putProduct(['id' => $data['product_id'], "stock" => $result[0]['stock']]);
                $data['status'] = 'paid';
                $result =  $this->mongo->store->orders->insertOne($data);
                $id =  $result->getInsertedId();
                $id = (array)$id;


                return ['message' => "orders created successfully with id -" . $id['oid']];
            } else {
                return ['error' => "available stock " . $result[0]['stock']];
            }
        } else {
            return ['error' => "product not found"];
        }
    }



    /**
     * function to update status of order
     *
     * @param [type] $data
     * @return void
     */
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
