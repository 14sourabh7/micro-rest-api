<?php

namespace App\Db;

use Phalcon\Di\Injectable;
use GuzzleHttp\Client;

class ProductHelper extends Injectable
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
     * search($key, $keyword)
     * 
     * function to search 
     *
     * @param [type] $key
     * @param [type] $keyword
     * @return json
     */
    public function search($keyword)
    {
        $keywords = [];

        foreach ($keyword as $key => $value) {
            array_push($keywords, array('name' => ['$regex' => $value]));
            array_push($keywords, array('variations' => [
                '$elemMatch' => [
                    "name" => [
                        '$regex' => $value
                    ]
                ]
            ]));
        }

        $result =  $this->mongo->store->products->find(['$or' => $keywords]);

        return $this->setResponse($result);
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
        $result = $this->mongo->store->products->find();

        return $this->setResponse($result);
    }

    /**
     * getSingle($id)
     * 
     * function returning all the products
     *
     * @param [type] $key
     * @return json
     */
    public function getSingle($id)
    {
        $id = $this->createID($id);
        $result = $this->mongo->store->products->find(["_id" => $id]);
        return $this->setResponse($result);
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
    public function get($per_page, $page, $select, $filter)
    {

        $columns = ["_id" => 0];

        foreach (explode(" ", urldecode($select)) as $column => $value) {
            $columns[$value] = 1;
        }


        $count =  $this->mongo->store->products->find(['name' => ['$regex' => $filter]]);
        $count = count(iterator_to_array($count));
        $pages = $count / $per_page;
        if ($pages < $page) {
            return (["info" => "page limit exceeded available data:$count"]);
        } else if ($page == 0) {
            return (["info" => "page number starts from 1"]);
        } else {
            $result = $this->mongo->store->products->find(['name' => ['$regex' => $filter]], [
                "skip" => (int)$page == 1 ? 0 : ((int) $page) - 1,
                "limit" => (int) $per_page,
                "projection" => $columns
            ]);
            $result =  $this->setResponse($result);
            if ($page == $pages) {
                $result['next_page'] = false;
            } else {
                $result['next_page'] = true;
            }
            return $result;
        }
        // die;

    }

    /**
     * function to add product in db
     *
     * @param [type] $data
     * @return void
     */
    public function postProduct($data)
    {

        $result =  $this->mongo->store->products->insertOne($data);
        $id = $result->getInsertedId();
        $this->sendWebhookResponse($id, 'insert', []);
        return $result;
    }


    /**
     * function to send webhook response
     *
     * @return void
     */
    public function sendWebhookResponse($id, $opr, $updatedfields)
    {

        $client = new Client();
        if ($opr !== 'delete')
            $products = $this->getSingle($id);
        $hooks = $this->mongo->store->webhooks->find([]);
        $hooks = iterator_to_array($hooks);
        foreach ($hooks as $key => $value) {
            $product['key'] = $value['secret'];
            $product['email'] = $value['email'];
            if ($opr == 'delete') {
                $product['del'] = $id;
            } else {
                if ($opr == 'update') {
                    $product['updates'] = $updatedfields;
                }
                $product['data'] = $products;
            }
            $product['opr'] = $opr;

            $client->request(
                'POST',
                urldecode($value['url']),
                ['form_params' => ["product" => $product]]
            );
        }
    }



    /**
     * function to update product in db
     *
     * @param [type] $data
     * @return void
     */
    public function putProduct($data)
    {

        $product =  $this->getSingle($data['id']);
        $updatedfields = array_diff($data, $product);
        $result =  $this->mongo->store->products->updateOne(
            [

                '_id' => $this->createID($data['id'])
            ],
            [
                '$set' => $data
            ]
        );

        $this->sendWebhookResponse($data['id'], 'update', $updatedfields);
        return $result;
    }



    /**
     * function to delete product from db
     *
     * @param [type] $id
     * @return void
     */
    public function deleteProduct($id)
    {

        $result =  $this->mongo->store->products->deleteOne(
            [
                '_id' => $this->createID($id)
            ]
        );
        $this->sendWebhookResponse($id, 'delete', []);
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
        $response = $result->toArray();
        $response = json_encode($response);
        $response = str_replace('$oid', 'oid', $response);
        $response = json_decode($response, TRUE);

        if (count($response) < 1) {
            $response = array("info" => "no data found for the given conditions");
        }
        return $response;
    }
}
