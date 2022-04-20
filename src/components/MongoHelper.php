<?php

namespace App\Db;

use Phalcon\Di\Injectable;

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
